<?php
namespace App\Services;

use App\Models\GoogleCalanderAccounts;
use Carbon\Carbon;
use Google_Client;
use Google_Service_Calendar;

class GoogleCalendarService
{
    protected Google_Client $client;
    private ?GoogleCalanderAccounts $googleCalanderAccounts = null;
    
    /**
     * Constructor that loads company Google Calendar account (ID=2)
     */
    public function __construct()
    {
        $this->client = new Google_Client();
        $this->client->setClientId(config('services.google.client_id'));
        $this->client->setClientSecret(config('services.google.client_secret'));
        $this->client->setRedirectUri(config('services.google.redirect'));
        $this->client->addScope(Google_Service_Calendar::CALENDAR);
        
        // Always load the company account (ID=2)
        $this->googleCalanderAccounts = GoogleCalanderAccounts::where('user_id', 2)->first();
    }

    /**
     * Get the Google client with proper authentication
     *
     * @return Google_Client
     */
    public function getClient(): Google_Client
    {
        // Ensure the company has an access token and refresh token
        if (!$this->googleCalanderAccounts || !$this->googleCalanderAccounts->access_token || !$this->googleCalanderAccounts->refresh_token) {
            \Log::error('Google Calendar account not found for company account.', ['company_id' => 2]);
            abort(response()->json([
                'redirect' => route('google.meet.login'),
                'message' => 'Company Google Calendar not connected. Please authenticate.'
            ], 401));
        }
        
        // Set the access token and refresh token
        $this->client->setAccessToken([
            'access_token' => $this->googleCalanderAccounts->access_token,
            'refresh_token' => $this->googleCalanderAccounts->refresh_token,
            'expires_in' => Carbon::parse($this->googleCalanderAccounts->expires_at)
                ->diffInSeconds(Carbon::now(), false), // Ensure negative values are handled
        ]);

        // If the access token is expired, refresh it
        if ($this->client->isAccessTokenExpired()) {
            try {
                $newToken = $this->client->fetchAccessTokenWithRefreshToken(
                    $this->googleCalanderAccounts->refresh_token
                );

                if (isset($newToken['access_token'])) {
                    // Update the company tokens in the database
                    $this->googleCalanderAccounts->update([
                        'access_token' => $newToken['access_token'],
                        'refresh_token' => $newToken['refresh_token'] ?? $this->googleCalanderAccounts->refresh_token,
                        'expires_at' => Carbon::now()->addSeconds($newToken['expires_in']),
                        'created' => strtotime($this->googleCalanderAccounts->updated_at),
                    ]);

                    // Set the new access token on the client
                    $this->client->setAccessToken($newToken);
                } else {
                    \Log::error('Failed to refresh Google token for company account.', ['company_id' => 2]);
                    abort(response()->json([
                        'redirect' => route('google.meet.login'),
                        'message' => 'Failed to refresh Google token, please authenticate again.'
                    ], 401));
                }
            } catch (\Exception $e) {
                \Log::error('Google token refresh failed for company account: ' . $e->getMessage(), ['company_id' => 2]);
                abort(response()->json([
                    'redirect' => route('google.meet.login'),
                    'message' => 'Error refreshing Google token, please authenticate again.'
                ], 401));
            }
        }

        return $this->client;
    }

    /**
     * List events from the Google Calendar
     *
     * @return array
     */
    public function listEvents(): array
    {
        $service = new Google_Service_Calendar($this->getClient());

        $optParams = [
            'maxResults' => 100,
            'orderBy' => 'startTime',
            'singleEvents' => true,
            'timeMin' => now()->toRfc3339String(),
        ];

        $events = $service->events->listEvents('primary', $optParams);

        return collect($events->getItems())->map(function ($event) {
            return [
                'title' => $event->getSummary(),
                'start' => $event->getStart()->getDateTime() ?? $event->getStart()->getDate(),
                'end' => $event->getEnd()->getDateTime() ?? $event->getEnd()->getDate(),
                'hangoutLink' => $event->getHangoutLink(),
            ];
        })->toArray();
    }
}