<?php

namespace Modules\RestAPI\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Modules\RestAPI\Entities\Leave;
use Modules\RestAPI\Entities\LeaveType;
use Modules\RestAPI\Http\Requests\Leave\ApplyRequest;
use Modules\RestAPI\Http\Requests\Leave\CreateRequest;
use Modules\RestAPI\Http\Requests\Leave\DeleteRequest;
use Modules\RestAPI\Http\Requests\Leave\IndexRequest;
use Modules\RestAPI\Http\Requests\Leave\ShowRequest;
use Modules\RestAPI\Http\Requests\Leave\UpdateRequest;

class LeaveController extends ApiBaseController
{
    protected $model = Leave::class;

    protected $indexRequest = IndexRequest::class;

    protected $storeRequest = CreateRequest::class;

    protected $updateRequest = UpdateRequest::class;

    protected $showRequest = ShowRequest::class;

    protected $deleteRequest = DeleteRequest::class;

    public function modifyIndex($query)
    {
        return $query->visibility()
            ->join(
                \DB::raw('(SELECT `id` as `a_user_id`, `name` as `employee_name` FROM `users`) as `a`'),
                'a.a_user_id',
                '=',
                'leaves.user_id'
            );
    }
    public function apply(ApplyRequest $request): JsonResponse
{
    $user = api_user();

    // ✅ Check user and module
    if (!$user || !isset($user->modules) || !in_array('leaves', (array) $user->modules)) {
        return response()->json(['message' => 'Leaves module is not enabled for this user.'], 403);
    }

    $data = $request->only([
        'leave_type_id',
        'leave_date',
        'start_date',
        'end_date',
        'reason',
        'half_day',
        'status',
    ]);

    $leaveTypeId = $data['leave_type_id'] ?? null;
    $rawStartDate = $data['start_date'] ?? $data['leave_date'] ?? null;
    $rawEndDate = $data['end_date'] ?? $data['leave_date'] ?? null;
    $reason = trim($data['reason'] ?? '');
    $halfDay = !empty($data['half_day']);
    $requestedStatus = $data['status'] ?? null;

    // ✅ Check for missing fields
    if (!$leaveTypeId || !$rawStartDate || !$rawEndDate) {
        return response()->json(['message' => 'Missing required fields.'], 422);
    }

    try {
        $start = Carbon::parse($rawStartDate)->startOfDay();
        $end = Carbon::parse($rawEndDate)->startOfDay();
    } catch (\Exception $e) {
        return response()->json([
            'message' => 'Invalid date format.',
            'error' => $e->getMessage(),
        ], 422);
    }

    if ($end->lt($start)) {
        return response()->json(['message' => 'End date must be the same or after start date.'], 422);
    }

    $requestedDays = $halfDay ? 0.5 : ($end->diffInDays($start) + 1);

    // ✅ Load LeaveType safely
    $leaveType = LeaveType::find($leaveTypeId);
    if (!$leaveType) {
        return response()->json(['message' => 'Invalid leave type.'], 422);
    }

    // ✅ Safely handle both column naming possibilities
    $noOfLeaves = null;
    if (isset($leaveType->no_of_leaves) && is_numeric($leaveType->no_of_leaves)) {
        $noOfLeaves = (float) $leaveType->no_of_leaves;
    } 
    // elseif (isset($leaveType->leaves_remaining) && is_numeric($leaveType->leaves_remaining)) {
    //     $noOfLeaves = (float) $leaveType->leaves_remaining;
    // }

    $usedDays = 0.0;
    $remaining = null;

    if ($noOfLeaves !== null) {
        $existingLeaves = Leave::where('user_id', $user->id)
            ->where('leave_type_id', $leaveTypeId)
            ->whereIn('status', ['approved', 'pending'])
            ->get();

        foreach ($existingLeaves as $ex) {
            try {
                $exStartRaw = $ex->start_date ?? $ex->leave_date ?? null;
                $exEndRaw = $ex->end_date ?? $ex->leave_date ?? $ex->start_date ?? null;

                if (!$exStartRaw || !$exEndRaw) {
                    continue;
                }

                $exStart = Carbon::parse($exStartRaw)->startOfDay();
                $exEnd = Carbon::parse($exEndRaw)->startOfDay();
                $exHalf = !empty($ex->half_day);

                $usedDays += $exHalf ? 0.5 : ($exEnd->diffInDays($exStart) + 1);
            } catch (\Exception $inner) {
                continue;
            }
        }

        $remaining = max(0, $noOfLeaves - $usedDays); // ✅ Never negative

        $canBypass = optional($user)->hasRole('admin')
            || optional($user)->cans('add_leave')
            || optional($user)->cans('edit_leave')
            || optional($user)->cans('approve_leave');

        if (!$canBypass && $remaining < $requestedDays) {
            return response()->json([
                'message' => 'Insufficient leave balance.',
                'errors' => [
                    'remaining' => $remaining,
                    'requested' => $requestedDays,
                    'type_quota' => $noOfLeaves,
                ],
            ], 422);
        }
    }

    // ✅ Build payload with all null-safety
    $payload = [
        'user_id' => $user->id,
        'leave_type_id' => $leaveTypeId,
        'leave_date' => $start->toDateString(),
        'start_date' => $start->toDateString(),
        'end_date' => $end->toDateString(),
        'duration' => $halfDay ? '0.5 day' : ($end->diffInDays($start) + 1) . ' days',
        'reason' => $reason,
        'half_day' => $halfDay ? 1 : 0,
        'status' => in_array($requestedStatus, ['pending', 'approved', 'rejected'])
            ? $requestedStatus
            : 'pending',
        'company_id' => optional($user)->company_id ?? optional($user->company)->id ?? null,
        'leaves_remaining' => $remaining
    ];

    if (empty($payload['company_id'])) {
        unset($payload['company_id']);
    }
    try {
        $leave = Leave::create($payload);
        return response()->json([
            'message' => 'Leave applied successfully.',
            'data' => $leave,
        ], 201);
    } catch (\Exception $e) {
        // ✅ Log the detailed error for debugging
        \Log::error('Leave apply failed', [
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString(),
            'payload' => $payload,
        ]);

        return response()->json([
            'message' => 'Failed to create leave.',
            'error' => $e->getMessage(),
            'payload' => $payload,
        ], 500);
    }
}
}
