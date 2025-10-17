<?php

namespace App\Http\Controllers;

use App\Models\Meeting;
use App\Models\LeadMeeting;
use App\Models\Deal;
use Illuminate\Http\Request;

class MeetingController extends Controller
{
    public function index()
    {
        $meetings = Meeting::latest()->get();
        return response()->json($meetings);
    }
    public function create(Request $request)
    {
        // dd($request->all());
        $leadId = $request->get('lead_id');

        $start = new \DateTime();
        // dd($start,date_default_timezone_get());
        $bookedMeetings = Meeting::whereDate('date', $start->format('Y-m-d'))->get(['time', 'end_time']);
        $deal = Deal::where('lead_id',$leadId)->first();
        $pipline = $deal->lead_pipeline_id;
        $end = new \DateTime('24:00');

        // Calculate the nearest 15-minute interval to the start time
        $minutes = (int)$start->format('i');
        $offset = (15 - ($minutes % 15)) % 15;
        $start->modify("+{$offset} minutes");

        // Generate available time slots
        $slots = [];
        // dd($start,$end);
        $fime = (int)15;
        while ($start < $end) {

            $isBooked =  $bookedMeetings->filter(function ($meeting) use ($start, $fime) {
                $slotStart = clone $start;
                $slotEnd = clone $start;
                $slotEnd->add(new \DateInterval("PT{$fime}M"));

                $meetingStart = \DateTime::createFromFormat('H:i', $meeting->time);
                $meetingEnd = \DateTime::createFromFormat('H:i', $meeting->end_time);

                // Calculate overlap duration
                $overlapStart = max($slotStart, $meetingStart);
                $overlapEnd = min($slotEnd, $meetingEnd);
                $overlapDuration = $overlapEnd->getTimestamp() - $overlapStart->getTimestamp();
                // Check if there is at least a 1-minute overlap
                return $overlapDuration >= 60;
            });


            if (!$isBooked->isNotEmpty()) {

                $slots[] = $start->format('h:i A'); // Format time as 12-hour with AM/PM
            }else{
                $meetingEndTimes = $isBooked->map(function ($meeting) {
                    return $meeting->end_time;
                });
                $firstOverlappingMeetingEndTime = $meetingEndTimes->first();
                $start = \DateTime::createFromFormat('H:i', $firstOverlappingMeetingEndTime);
                $slots[] = $start->format('h:i A');
            }

            $start->modify('+' . $fime . ' minutes');
        }

        return view('leads.ajax.meeting_create', compact('leadId','slots'));
    }
    public function availabletime(Request $request)
    {
        // dd($request->all());
        $fime = (int)$request->time;
        $providedDateTime = \DateTime::createFromFormat('d-m-Y', $request->date);

        $bookedMeetings = Meeting::whereDate('date', $providedDateTime->format('Y-m-d'))
            ->get(['time', 'end_time']); // Get start and end times

        $currentDate = new \DateTime();
        if ($providedDateTime->format('Y-m-d') === $currentDate->format('Y-m-d')) {
            $start = new \DateTime();
        } else {
            $start = new \DateTime('00:00');
        }
        $end = new \DateTime('24:00');

        // Calculate the nearest 15-minute interval to the start time
        $minutes = (int)$start->format('i');
        $offset = ($fime - ($minutes % $fime)) % $fime;
        $start->modify("+{$offset} minutes");

        // Generate available time slots
        $slots = [];
        
        while ($start < $end) {

            $isBooked =  $bookedMeetings->filter(function ($meeting) use ($start, $fime) {
                $slotStart = clone $start;
                $slotEnd = clone $start;
                $slotEnd->add(new \DateInterval("PT{$fime}M")); // Add duration to get slot end time

                $meetingStart = \DateTime::createFromFormat('H:i', $meeting->time);
                $meetingEnd = \DateTime::createFromFormat('H:i', $meeting->end_time);

                // Calculate overlap duration
                $overlapStart = max($slotStart, $meetingStart);
                $overlapEnd = min($slotEnd, $meetingEnd);
                $overlapDuration = $overlapEnd->getTimestamp() - $overlapStart->getTimestamp();

                // Check if there is at least a 1-minute overlap
                return $overlapDuration >= 60;
            });


            if (!$isBooked->isNotEmpty()) {

                $slots[] = $start->format('h:i A'); // Format time as 12-hour with AM/PM
            }else{
                $meetingEndTimes = $isBooked->map(function ($meeting) {
                    return $meeting->end_time;
                });
                $firstOverlappingMeetingEndTime = $meetingEndTimes->first();
                $start = \DateTime::createFromFormat('H:i', $firstOverlappingMeetingEndTime);
                $slots[] = $start->format('h:i A');
            }

            $start->modify('+' . $fime . ' minutes');
        }

            $data = [
                'status' => 'success',
                'data' => $slots,
            ];

            return response()->json($data);
    }
    public function store(Request $request)
    {
        $validated = $request->validate([
            'lead_id' => 'nullable|integer',
            'user_id' => 'nullable|integer',
            'meeting_date' => 'required|date',
            'meeting_time' => 'required',
            'meeting_minutes' => 'nullable|string',
            'join_url' => 'nullable|url',
        ]);

        $meeting = Meeting::create($validated);

        return response()->json(['success' => true, 'data' => $meeting]);
    }

    public function update(Request $request, $id)
    {
        $meeting = Meeting::findOrFail($id);

        $validated = $request->validate([
            'meeting_date' => 'required|date',
            'meeting_time' => 'required',
            'meeting_minutes' => 'nullable|string',
            'join_url' => 'nullable|url',
        ]);

        $meeting->update($validated);

        return response()->json(['success' => true]);
    }

    public function destroy($id)
    {
        Meeting::findOrFail($id)->delete();

        return response()->json(['success' => true]);
    }
}