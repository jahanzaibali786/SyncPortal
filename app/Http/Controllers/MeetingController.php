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
        $leadId = $request->get('lead_id');

        $start = new \DateTime();
        $end = (clone $start)->setTime(23, 59); // end of day safely

        // Get all booked meetings for today
        $bookedMeetings = Meeting::whereDate('date', $start->format('Y-m-d'))
            ->get(['time', 'end_time']);

        $deal = Deal::where('lead_id', $leadId)->first();
        $pipeline = $deal?->lead_pipeline_id;

        // Round start time to next 15-minute slot
        $minutes = (int) $start->format('i');
        $offset = (15 - ($minutes % 15)) % 15;
        $start->modify("+{$offset} minutes");

        $intervalMinutes = 15;
        $slots = [];

        while ($start < $end) {
            $slotStart = clone $start;
            $slotEnd = (clone $start)->add(new \DateInterval("PT{$intervalMinutes}M"));

            $isBooked = $bookedMeetings->contains(function ($meeting) use ($slotStart, $slotEnd) {
                // Parse meeting times safely (try multiple formats)
                $meetingStart = \DateTime::createFromFormat('H:i', $meeting->time)
                    ?: \DateTime::createFromFormat('H:i:s', $meeting->time)
                    ?: \DateTime::createFromFormat('Y-m-d H:i:s', $meeting->time);

                $meetingEnd = \DateTime::createFromFormat('H:i', $meeting->end_time)
                    ?: \DateTime::createFromFormat('H:i:s', $meeting->end_time)
                    ?: \DateTime::createFromFormat('Y-m-d H:i:s', $meeting->end_time);

                // If parsing fails, skip this meeting
                if (!$meetingStart || !$meetingEnd) {
                    return false;
                }

                // Overlap check
                $latestStart = $slotStart > $meetingStart ? $slotStart : $meetingStart;
                $earliestEnd = $slotEnd < $meetingEnd ? $slotEnd : $meetingEnd;

                $overlap = $earliestEnd->getTimestamp() - $latestStart->getTimestamp();

                // At least 1 minute overlap means slot is taken
                return $overlap >= 60;
            });

            if (!$isBooked) {
                $slots[] = $start->format('h:i A');
            }

            $start->modify("+{$intervalMinutes} minutes");
        }

        return view('leads.ajax.meeting_create', compact('leadId', 'slots'));
    }

    public function availabletime(Request $request)
    {
        $slotDuration = (int) $request->time ?? 15; // in minutes
        $providedDate = \DateTime::createFromFormat('d-m-Y', $request->date);

        if (!$providedDate) {
            return response()->json([
                'status' => 'error',
                'message' => 'Invalid date format. Expected d-m-Y.',
            ], 422);
        }

        // Fetch booked meetings for that date
        $bookedMeetings = Meeting::whereDate('date', $providedDate->format('Y-m-d'))
            ->get(['time', 'end_time']);

        $now = new \DateTime();
        $start = ($providedDate->format('Y-m-d') === $now->format('Y-m-d'))
            ? clone $now
            : (clone $providedDate)->setTime(0, 0);

        $end = (clone $providedDate)->setTime(23, 59);

        // Round start time up to nearest slot interval
        $minutes = (int) $start->format('i');
        $offset = ($slotDuration - ($minutes % $slotDuration)) % $slotDuration;
        $start->modify("+{$offset} minutes");

        $slots = [];

        while ($start < $end) {
            $slotStart = clone $start;
            $slotEnd = (clone $slotStart)->add(new \DateInterval("PT{$slotDuration}M"));

            // Skip past times (only for current day)
            if ($providedDate->format('Y-m-d') === $now->format('Y-m-d') && $slotStart < $now) {
                $start->modify("+{$slotDuration} minutes");
                continue;
            }

            // Check if slot overlaps any booked meeting
            $isBooked = $bookedMeetings->contains(function ($meeting) use ($slotStart, $slotEnd) {
                $meetingStart = \DateTime::createFromFormat('H:i', $meeting->time)
                    ?: \DateTime::createFromFormat('H:i:s', $meeting->time)
                    ?: \DateTime::createFromFormat('Y-m-d H:i:s', $meeting->time);

                $meetingEnd = \DateTime::createFromFormat('H:i', $meeting->end_time)
                    ?: \DateTime::createFromFormat('H:i:s', $meeting->end_time)
                    ?: \DateTime::createFromFormat('Y-m-d H:i:s', $meeting->end_time);

                // Skip invalid entries
                if (!$meetingStart || !$meetingEnd) {
                    return false;
                }

                // Overlap detection
                $latestStart = $slotStart > $meetingStart ? $slotStart : $meetingStart;
                $earliestEnd = $slotEnd < $meetingEnd ? $slotEnd : $meetingEnd;

                $overlap = $earliestEnd->getTimestamp() - $latestStart->getTimestamp();

                // At least 1-minute overlap => booked
                return $overlap >= 60;
            });

            if (!$isBooked) {
                $slots[] = $slotStart->format('h:i A');
            }

            $start->modify("+{$slotDuration} minutes");
        }

        return response()->json([
            'status' => 'success',
            'data' => $slots,
        ]);
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