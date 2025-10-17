<?php

namespace App\Http\Controllers;

use App\Models\GoogleCalanderAccounts;
use App\Models\GoogleMeetings;
use App\Models\Lead;
use App\Models\Meeting;
use App\Models\User;
use App\Services\GoogleCalendarService;
use Carbon\Carbon;
use Google_Service_Calendar_ConferenceData;
use Illuminate\Http\Request;
use Google_Client;
use Google_Service_Calendar;
use Google_Service_Calendar_Event;
use Google_Service_Calendar_EventDateTime;
use Google_Service_Calendar_EventAttendee;
use Google_Service_Drive;
use Laravel\Socialite\Facades\Socialite;

class GoogleMeetController extends Controller
{
    // Fixed co-host email - you can make this dynamic later
    private $cohostEmail = '33384@iqraisb.edu.pk,abdulhakeemkhan13@gmail.com,uzairaftab332211@gmail.com';

    public function redirectToGoogle()
    {
        // Socialite sets redirect_uri from config/services.php and manages state
        return Socialite::driver('google')
            ->scopes([
                Google_Service_Calendar::CALENDAR,
                Google_Service_Calendar::CALENDAR_EVENTS,
                // any other scopes you need
            ])
            ->with(['access_type' => 'offline', 'prompt' => 'consent'])
            ->redirect();
    }

    public function handleGoogleCallback()
    {
        // dd($request->all());
        // Drop stateless() so Socialite validates state
        try {
            $socialUser = Socialite::driver('google')->user();
        } catch (\Laravel\Socialite\Two\InvalidStateException $e) {
            return redirect()->route('google.meet.login')
                ->with('error', 'Invalid OAuth state, please try again.');
        } catch (\Exception $e) {
            \Log::error('Google OAuth error: ' . $e->getMessage());
            return redirect()->route('google.meet.login')
                ->with('error', 'Google OAuth failed.');
        }
        // Check if a refresh token is available
        $refreshToken = $socialUser->refreshToken;
        if (!$refreshToken) {
            $socialUser = Socialite::driver('google')->userFromToken($socialUser->token);
            $refreshToken = $socialUser->refreshToken;
        }
        // Persist tokens as before:
        $account = GoogleCalanderAccounts::updateOrCreate(
            ['user_id' => auth()->id()],
            [
                'google_account_id' => $socialUser->id,
                'access_token' => $socialUser->token,
                'refresh_token' => $socialUser->refreshToken,
                'expires_at' => now()->addSeconds($socialUser->expiresIn),
            ]
        );

        session(['google_token' => $socialUser->token]);
        $calendarService = new GoogleCalendarService(); // create object
        $this->syncLocalMeetingsToGoogle($calendarService);
        return redirect()->route('deals')
            ->with('success', 'Google Meet access granted. Now you create Meeting.');
    }

    public function googleCalander(GoogleCalendarService $calendar)
    {
        $events = $calendar->listEvents();
        $this->syncLocalMeetingsToGoogle($calendar);
        return response()->json($events);
    }

    public function showForm()
    {
        return view('google_meet.form');
    }

    protected function syncLocalMeetingsToGoogle(GoogleCalendarService $calendarService)
    {
        $meetings = GoogleMeetings::whereJsonContains('assigned_to', auth()->user()->id)->get();
        if ($meetings->isEmpty()) {
            return;
        }

        $client = $calendarService->getClient();
        $service = new \Google_Service_Calendar($client);

        try {
            // Step 1: Fetch all existing Google Calendar events
            $googleEvents = [];
            $pageToken = null;

            do {
                $events = $service->events->listEvents('primary', [
                    'pageToken' => $pageToken,
                    'singleEvents' => true,
                    'orderBy' => 'startTime',
                ]);
                $googleEvents = array_merge($googleEvents, $events->getItems());
                $pageToken = $events->getNextPageToken();
            } while ($pageToken);

            // Collect all existing event summaries and descriptions
            $existingMeetLinks = [];
            foreach ($googleEvents as $event) {
                if (isset($event->hangoutLink)) {
                    $existingMeetLinks[] = $event->hangoutLink;
                }
                if (isset($event->description)) {
                    $existingMeetLinks[] = $event->description;
                }
            }

            // Step 2: Compare and Insert Only Unique Meetings
            foreach ($meetings as $meeting) {
                $meetingLink = $meeting->google_meet_link ?? '';
                $meetingDescription = ($meeting->google_meet_link ? 'Meeting Link: ' . $meeting->google_meet_link . "\n\n" : '') . $meeting->description;

                // Check if meeting already exists
                if (in_array($meetingLink, $existingMeetLinks) || in_array($meetingDescription, $existingMeetLinks)) {
                    continue; // skip if already exists
                }

                // Step 3: Create New Event with Co-hosts if not exist
                $attendees = [];
                if ($this->cohostEmail) {
                    $emailList = array_map('trim', explode(',', $this->cohostEmail));
                    foreach ($emailList as $email) {
                        if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
                            $attendees[] = new Google_Service_Calendar_EventAttendee([
                                'email' => $email,
                                'responseStatus' => 'needsAction'
                            ]);
                        }
                    }
                }

                $event = new \Google_Service_Calendar_Event([
                    'summary' => $meeting->title,
                    'description' => $meetingDescription,
                    'start' => [
                        'dateTime' => \Carbon\Carbon::parse($meeting->start)->toRfc3339String(),
                        'timeZone' => 'Asia/Karachi',
                    ],
                    'end' => [
                        'dateTime' => \Carbon\Carbon::parse($meeting->end)->toRfc3339String(),
                        'timeZone' => 'Asia/Karachi',
                    ],
                    'attendees' => $attendees,
                    'conferenceData' => [
                        'createRequest' => [
                            'conferenceSolutionKey' => ['type' => 'hangoutsMeet'],
                            'requestId' => uniqid(),
                        ],
                    ],
                ]);

                $createdEvent = $service->events->insert('primary', $event, [
                    'conferenceDataVersion' => 1,
                    'sendUpdates' => 'all' // Send invitations to attendees
                ]);
            }
        } catch (\Exception $e) {
            \Log::error('Google Calendar Sync Error: ' . $e->getMessage());
            // You can also dd($e) if you want during testing
        }
    }

    // public function createMeet(Request $request, GoogleCalendarService $calendarService)
    // {
    //     $validator = \Validator::make($request->all(), [
    //         'description' => 'nullable|string',
    //         'lead_id' => 'required',
    //         'user_id' => 'nullable',
    //         'time' => 'required',
    //         'mint' => 'required|integer',
    //         'date' => 'required|date',
    //         'cohost_email' => 'nullable|email',
    //     ]);
    //     // dd($request->all());
    //     if ($validator->fails()) {
    //         $messages = $validator->getMessageBag();
    //         return back()->with('error', $messages->first());
    //     }
    //     try {
    //         // Get the company Google client
    //         $client = $calendarService->getClient();
    //         $service = new Google_Service_Calendar($client);

    //         $startTime = Carbon::parse($request->date . ' ' . $request->time);
    //         $endTime = $startTime->copy()->addMinutes($request->mint);

    //         // Get lead name
    //         $lead = Lead::findOrFail($request->lead_id);
    //         $title = $lead->name;

    //         // Prepare attendees array for co-host
    //         $attendees = [];
    //         $cohostEmailToUse = $request->cohost_email ?? $this->cohostEmail;
    //         if ($cohostEmailToUse) {
    //             $emailList = array_map('trim', explode(',', $cohostEmailToUse));
    //             foreach ($emailList as $email) {
    //                 if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
    //                     $attendees[] = new Google_Service_Calendar_EventAttendee([
    //                         'email' => $email,
    //                         'responseStatus' => 'needsAction'
    //                     ]);
    //                 }
    //             }
    //         }

    //         $event = new Google_Service_Calendar_Event([
    //             'summary' => $title,
    //             'description' => $request->description,
    //             'start' => [
    //                 'dateTime' => $startTime->toRfc3339String(),
    //                 'timeZone' => 'Asia/Karachi',
    //             ],
    //             'end' => [
    //                 'dateTime' => $endTime->toRfc3339String(),
    //                 'timeZone' => 'Asia/Karachi',
    //             ],
    //             'attendees' => $attendees,
    //             'conferenceData' => [
    //                 'createRequest' => [
    //                     'conferenceSolutionKey' => ['type' => 'hangoutsMeet'],
    //                     'requestId' => uniqid(),
    //                 ],
    //             ],
    //         ]);

    //         $createdEvent = $service->events->insert('primary', $event, [
    //             'conferenceDataVersion' => 1,
    //             'sendUpdates' => 'all' // Send invitations to attendees including co-host
    //         ]);

    //         if ($createdEvent->getStatus() == 'confirmed') {
    //             // Store in your database
    //             GoogleMeetings::create([
    //                 'title' => $title,
    //                 'lead_id' => $request->lead_id,
    //                 'start' => $startTime,
    //                 'end' => $endTime,
    //                 'description' => $request->description,
    //                 'assigned_to' => auth()->check() ? auth()->id() : decrypt($request->user_id),
    //                 'google_meet_link' => $createdEvent->getHangoutLink(),
    //                 'google_meet_id' => $createdEvent->getId(),
    //                 'google_meet_password' => optional($createdEvent->getConferenceData()->getEntryPoints()[0])->getUri(),
    //                 'cohost_email' => $cohostEmailToUse, // Store co-host email
    //             ]);

    //             return redirect()->route('google.calander.view')
    //                 ->with('success', 'Google Meet created successfully with co-host invitation sent.');
    //         } else {
    //             return back()->with('error', 'Failed to create Google Meet.');
    //         }

    //     } catch (\Exception $e) {
    //         if (method_exists($e, 'getResponse') && $e->getResponse()) {
    //             $response = $e->getResponse();
    //             if (method_exists($response, 'getContent')) {
    //                 $content = $response->getContent(); // JSON string
    //                 $data = json_decode($content, true); // Convert to PHP array

    //                 if (isset($data['redirect'])) {
    //                     return redirect($data['redirect'])
    //                         ->with('error', $data['message'] ?? 'Authentication required. Please connect Google Calendar.');
    //                 }
    //             }
    //         }

    //         \Log::error('Error creating Google Meet: ' . $e->getMessage());
    //         return back()->with('error', 'Error creating Google Meet: ' . $e->getMessage());
    //     }
    // }
    public function createMeet(Request $request, GoogleCalendarService $calendarService)
    {
        $validator = \Validator::make($request->all(), [
            'description' => 'nullable|string',
            'lead_id' => 'required',
            'user_id' => 'nullable',
            'time' => 'required',
            'mint' => 'required|integer',
            'date' => 'required|date_format:d-m-Y',
            'cohost_email' => 'nullable|email',
        ]);

        if ($validator->fails()) {
            $messages = $validator->getMessageBag();
            return response()->json([
                'status' => 'error',
                'message' => $messages->first()
            ], 422);
        }

        try {
            // Get the company Google client
            $client = $calendarService->getClient();
            $service = new Google_Service_Calendar($client);

            // Parse date from d-m-Y format
            $dateObj = \Carbon\Carbon::createFromFormat('d-m-Y', $request->date);
            $startTime = $dateObj->setTimeFromTimeString($request->time);
            $endTime = $startTime->copy()->addMinutes($request->mint);

            // Get lead name
            $lead = Lead::where('lead_id', $request->lead_id)->first();
            $title = $lead->company_name;

            // Prepare attendees array for co-host
            $attendees = [];
            $cohostEmailToUse = $request->cohost_email ?? $this->cohostEmail;
            if ($cohostEmailToUse) {
                $emailList = array_map('trim', explode(',', $cohostEmailToUse));
                foreach ($emailList as $email) {
                    if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
                        $attendees[] = new Google_Service_Calendar_EventAttendee([
                            'email' => $email,
                            'responseStatus' => 'needsAction'
                        ]);
                    }
                }
            }

            $event = new Google_Service_Calendar_Event([
                'summary' => $title,
                'description' => $request->description,
                'start' => [
                    'dateTime' => $startTime->toRfc3339String(),
                    'timeZone' => 'Asia/Karachi',
                ],
                'end' => [
                    'dateTime' => $endTime->toRfc3339String(),
                    'timeZone' => 'Asia/Karachi',
                ],
                'attendees' => $attendees,
                'conferenceData' => [
                    'createRequest' => [
                        'conferenceSolutionKey' => ['type' => 'hangoutsMeet'],
                        'requestId' => uniqid(),
                    ],
                ],
            ]);

            $createdEvent = $service->events->insert('primary', $event, [
                'conferenceDataVersion' => 1,
                'sendUpdates' => 'all' // Send invitations to attendees including co-host
            ]);

            if ($createdEvent->getStatus() == 'confirmed') {
                // Store in your database
               $gmeet = GoogleMeetings::create([
                    'title' => $title,
                    'lead_id' => $request->lead_id,
                    'start' => $startTime,
                    'end' => $endTime,
                    'description' => $request->description,
                    'assigned_to' => auth()->check() ? auth()->id() : decrypt($request->user_id),
                    'google_meet_link' => $createdEvent->getHangoutLink(),
                    'google_meet_id' => $createdEvent->getId(),
                    'google_meet_password' => optional($createdEvent->getConferenceData()->getEntryPoints()[0])->getUri(),
                    'cohost_email' => $cohostEmailToUse,
                ]);
                if($gmeet){
                    //meeting create
                    $meeting = new Meeting();
                    $meeting->lead_id = $request->lead_id;
                    $meeting->user_id = auth()->check() ? auth()->id() : decrypt($request->user_id);
                    $meeting->name = $title;
                    $meeting->email = $cohostEmailToUse;
                    $meeting->date = $dateObj;
                    $meeting->time = $startTime;
                    $meeting->total_min = $request->mint;
                    $meeting->meeting_id = $gmeet->id;
                    $meeting->password = $gmeet->google_meet_password;
                    $meeting->start_url = $gmeet->google_meet_link;
                    $meeting->join_url = $gmeet->google_meet_link;
                    $meeting->meeting_date = $dateObj;
                    $meeting->meeting_time = $startTime;
                    $meeting->meeting_minutes = $request->mint;
                    $meeting->status = 'Scheduled';
                    $meeting->save();
                }
                return response()->json([
                    'status' => 'success',
                    'message' => 'Google Meet created successfully with co-host invitation sent.',
                    'redirect' => route('deals.show', $request->lead_id . '?tab=meeting')
                ]);
            } else {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Failed to create Google Meet.'
                ], 500);
            }

        } catch (\Exception $e) {
            \Log::error('Error creating Google Meet: ' . $e->getMessage());

            // Check if it's a Google authentication error
            if (method_exists($e, 'getResponse') && $e->getResponse()) {
                $response = $e->getResponse();
                if (method_exists($response, 'getContent')) {
                    $content = $response->getContent();
                    $data = json_decode($content, true);

                    if (isset($data['redirect'])) {
                        return response()->json([
                            'status' => 'error',
                            'message' => $data['message'] ?? 'Authentication required. Please connect Google Calendar.',
                            'redirect' => $data['redirect']
                        ], 401);
                    }
                }
            }

            return response()->json([
                'status' => 'error',
                'message' => 'Error creating Google Meet: ' . $e->getMessage()
            ], 500);
        }
    }
    public function edit($id)
    {
        $meeting = GoogleMeetings::findOrFail($id);
        return view('google_meet.edit', compact('meeting'));
    }

    public function updatemeeting(Request $request, $id, GoogleCalendarService $calendarService)
    {
        $meeting = GoogleMeetings::findOrFail($id);

        // Validate co-host email if provided
        $request->validate([
            'cohost_email' => 'nullable',
        ]);

        try {
            $client = $calendarService->getClient();
            $service = new Google_Service_Calendar($client);

            $startTime = Carbon::parse($request->start_time);
            $endTime = Carbon::parse($request->end_time);

            $lead = Lead::findOrFail($meeting->lead_id);
            $title = @$request->title ?? $lead->name;

            $event = $service->events->get('primary', $meeting->google_meet_id);

            // Prepare attendees array for co-host
            $attendees = [];
            $attendees = [];
            $cohostEmailToUse = $request->cohost_email ?? $meeting->cohost_email ?? $this->cohostEmail;

            if ($cohostEmailToUse) {
                $emailList = array_map('trim', explode(',', $cohostEmailToUse));
                foreach ($emailList as $email) {
                    if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
                        $attendees[] = new Google_Service_Calendar_EventAttendee([
                            'email' => $email,
                            'responseStatus' => 'needsAction'
                        ]);
                    }
                }
            }

            // Update event details
            $event->setSummary($title);
            $event->setDescription($request->description);
            $event->setAttendees($attendees);
            $event->setStart(new Google_Service_Calendar_EventDateTime([
                'dateTime' => $startTime->toRfc3339String(),
                'timeZone' => 'Asia/Karachi',
            ]));
            $event->setEnd(new Google_Service_Calendar_EventDateTime([
                'dateTime' => $endTime->toRfc3339String(),
                'timeZone' => 'Asia/Karachi',
            ]));

            // Optional: If you want to regenerate a new Meet link, you must **remove** the old one and add a new createRequest
            $event->setConferenceData(new Google_Service_Calendar_ConferenceData([
                'createRequest' => [
                    'conferenceSolutionKey' => ['type' => 'hangoutsMeet'],
                    'requestId' => uniqid(),
                ],
            ]));

            // Save updates to Google
            $updatedEvent = $service->events->update('primary', $event->getId(), $event, [
                'conferenceDataVersion' => 1,
                'sendUpdates' => 'all' // Send updates to attendees including co-host
            ]);

            // Update in your DB
            GoogleMeetings::where('google_meet_id', $meeting->google_meet_id)->update([
                'title' => $title,
                'start' => $startTime,
                'end' => $endTime,
                'description' => $request->description,
                'google_meet_link' => $updatedEvent->getHangoutLink(),
                'google_meet_password' => optional($updatedEvent->getConferenceData()->getEntryPoints()[0])->getUri(),
                'cohost_email' => $cohostEmailToUse, // Update co-host email
            ]);

            return redirect()->route('google.calander.view')
                ->with('success', 'Google Meet updated successfully with co-host invitation sent.');

        } catch (\Exception $e) {
            \Log::error('Error updating Google Meet: ' . $e->getMessage());
            return back()->with('error', 'Error updating Google Meet: ' . $e->getMessage());
        }
    }

    /**
     * Update co-host for a specific meeting
     */
    public function updateCohost(Request $request, $id, GoogleCalendarService $calendarService)
    {
        $request->validate([
            'cohost_email' => 'required',
        ]);

        $meeting = GoogleMeetings::findOrFail($id);

        try {
            $client = $calendarService->getClient();
            $service = new Google_Service_Calendar($client);

            $event = $service->events->get('primary', $meeting->google_meet_id);

            // Update attendees with new co-host
            $attendees = [
                new Google_Service_Calendar_EventAttendee([
                    'email' => $request->cohost_email,
                    'responseStatus' => 'needsAction'
                ])
            ];

            $event->setAttendees($attendees);

            // Save updates to Google
            $service->events->update('primary', $event->getId(), $event, [
                'sendUpdates' => 'all' // Send updates to attendees
            ]);

            // Update in your DB
            $meeting->update(['cohost_email' => $request->cohost_email]);

            return redirect()->back()->with('success', 'Co-host updated successfully and invitation sent.');

        } catch (\Exception $e) {
            \Log::error('Error updating co-host: ' . $e->getMessage());
            return back()->with('error', 'Error updating co-host: ' . $e->getMessage());
        }
    }

    private function getGoogleMeetRecordings()
    {
        $client = new \Google_Client();
        $client->setAccessToken(session('google_token'));
        $client->addScope(Google_Service_Drive::DRIVE_READONLY);

        $driveService = new \Google_Service_Drive($client);

        $optParams = [
            'q' => "name contains 'Meet' and mimeType='video/mp4'",
            'orderBy' => 'createdTime desc',
            'pageSize' => 5,
            'fields' => 'files(id, name, createdTime, webViewLink, webContentLink)'
        ];

        $results = $driveService->files->listFiles($optParams);

        return $results->getFiles(); // return as array
    }

    private function getClient()
    {
        $client = new Google_Client();
        $client->setClientId(env('GOOGLE_CLIENT_ID'));
        $client->setClientSecret(env('GOOGLE_CLIENT_SECRET'));
        $client->setRedirectUri(env('GOOGLE_REDIRECT_URI'));
        $client->addScope(Google_Service_Calendar::CALENDAR);
        $client->setAccessType('offline');
        $client->setPrompt('select_account consent');
        $client->addScope([
            Google_Service_Calendar::CALENDAR,
            Google_Service_Drive::DRIVE_READONLY,
            'email',
            'profile'
        ]);
        return $client;
    }

    public function assignUserModal(Request $request, $id)
    {
        $meeting = GoogleMeetings::find($id);
        $users = User::where('type', 'BDO')->get()->pluck('name', 'id');

        if (!$meeting) {
            return response()->json(['error' => 'Meeting not found'], 404);
        }

        // Check if 'assigned_to' is not empty and clean up the string
        $selectedUsers = [];
        if (!empty($meeting->assigned_to)) {
            // Remove unwanted characters (like brackets) and split by comma
            $assignedTo = str_replace(['[', ']', ' '], '', $meeting->assigned_to);
            $selectedUsers = explode(',', $assignedTo);
        }

        return view('google_meet.assign_user_modal', compact('meeting', 'users', 'selectedUsers'));
    }

    public function assignusers(Request $request, $id)
    {
        $meeting = GoogleMeetings::find($id);
        if (!$meeting) {
            return response()->json(['error' => 'Meeting not found'], 404);
        }
        $assignedUsers = $request->input('users');
        $meeting->assigned_to = implode(',', $assignedUsers);
        $meeting->save();

        return redirect()->back()->with('success', 'Meeting assigned successfully.');
    }
}