<?php

namespace Modules\RestAPI\Http\Controllers;

use App\Models\Project;
use App\Models\ProjectActivity;
use App\Models\ProjectTimeLog;
use App\Models\ProjectTimeLogBreak;
use App\Models\SubTask;
use App\Models\Task;
use App\Models\TaskboardColumn;
use App\Models\TaskHistory;
use App\Models\UserActivity;
use Carbon\Carbon;
use Froiden\RestAPI\ApiController;
use Modules\RestAPI\Entities\User;
use Modules\RestAPI\Http\Requests\Employee\IndexRequest;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

class TrakerController extends ApiBaseController 
{
    // public function __construct()
    // {
    //     $this->middleware('auth:api');

    // }
     public function logProjectActivity($projectId, $text)
    {
        $activity = new ProjectActivity();
        $activity->project_id = $projectId;
        $activity->activity = $text;
        $activity->save();
    }

    public function logUserActivity($userId, $text)
    {
        $activity = new UserActivity();
        $activity->user_id = $userId;
        $activity->activity = $text;
        $activity->save();
    }

    public function logTaskActivity($taskID, $userID, $text, $boardColumnId = null, $subTaskId = null)
    {
        $activity = new TaskHistory();
        $activity->task_id = $taskID;

        if (!is_null($subTaskId)) {
            $activity->sub_task_id = $subTaskId;
        }

        $activity->user_id = $userID;
        $activity->details = $text;

        if (!is_null($boardColumnId)) {
            $activity->board_column_id = $boardColumnId;
        }

        $activity->save();
    }

    public function autoMonthlyTask(Request $request)
    {
        DB::beginTransaction();
        $userId = auth()->id();
        $firstOfMonth = now()->startOfMonth();
        $user = User::where('id', $userId)->first();

        $project = Project::where('project_name', 'Global Project Tracker')->first(); // Replace with flag if you have 'is_global'
        if (!$project) {
            $project = new Project();
            $project->project_name = 'Global Project Tracker';
            $project->project_short_code = 'GP';
            $project->project_admin = $userId;
            $project->start_date = $firstOfMonth;
            $project->public_gantt_chart = 'enable';
            $project->public_taskboard = 'enable';
            $project->need_approval_by_admin = 0;
            $project->status = 'not started';
            $project->save();
        }
        // ✅ Check if a task for this month already exists        $user = User::where('id', $userId)->first();
        $TaskTitle = 'Task for ' . $firstOfMonth->format('F Y') . ' - ' . $user->name;
        $existingTask = Task::where('heading', $TaskTitle)->where('project_id', $project->id)->first();

        if ($existingTask) {
            $task = $existingTask;
        }else{
            // ✅ Create new monthly task
            $task = new Task();
            $task->heading = $TaskTitle;
            $task->description = 'Auto-generated task for ' . $firstOfMonth->format('F Y');
            $task->start_date = $firstOfMonth;
            $task->due_date = $firstOfMonth;
            $task->project_id = $project->id;
            $task->task_category_id = null; // Optional default
            $task->priority = 'medium';
            $task->board_column_id = TaskboardColumn::where('slug', 'doing')->first()->id;
            $task->is_private = 1;
            $task->billable = 0;
            $task->estimate_hours = 0;
            $task->estimate_minutes = 0;
            $task->repeat = 0;
            $task->save();
            if ($project) {
                $projectLastTaskCount = Task::projectTaskCount($project->id);
                if (isset($project->project_short_code)) {
                    $task->task_short_code = $project->project_short_code . '-' . $this->getTaskShortCode($project->project_short_code, $projectLastTaskCount);
                }
                else{
                    $task->task_short_code = $projectLastTaskCount + 1;
                }

                //  task_users assign this user to this task
                $task->users()->attach($userId);
            }
            $task->save();
        }

        try{
            $timeLog = new ProjectTimeLog();
            $activeTimer = ProjectTimeLog::selfActiveTimer();

            $activeTimer = ProjectTimeLog::selfActiveTimer();
            if ($activeTimer && is_null($activeTimer->activeBreak)) {
                DB::rollback();
                return response()->json([
                    'status' => 'error',
                    'message' => 'A timer is already running (ID: ' . $activeTimer->id . ').',
                    'timer_id' => $activeTimer->id,
                ]);
            }

            
            $timeLog->project_id = $project->id;
            $timeLog->task_id = $task->id;
            $timeLog->user_id = $user->id;
            $timeLog->start_time = now();
            $timeLog->hourly_rate = 0;
            // time add in memo
            $timeLog->memo = 'Auto-generated time log for ' . $firstOfMonth->format('H:i:s');
            $timeLog->save();

            // Log activities
            if ($project) {
                $this->logProjectActivity($project->id, 'modules.tasks.timerStartedBy');
                $this->logUserActivity($user->id, 'modules.tasks.timerStartedProject');
            } else {
                $this->logUserActivity($user->id, 'modules.tasks.timerStartedTask');
            }

            $this->logTaskActivity($timeLog->task_id, $user->id, 'timerStartedBy');

            /** @phpstan-ignore-next-line */

            // $this->activeTimerCount = ProjectTimeLog::whereNull('end_time')
            //     ->join('users', 'users.id', '=', 'project_time_logs.user_id')
            //     ->select('project_time_logs.id');

            // if ($this->viewTimelogPermission != 'all' && manage_active_timelogs() != 'all') {
            //     $this->activeTimerCount->where('project_time_logs.user_id', $user->id);
            // }

            // $this->activeTimerCount = $this->activeTimerCount->count();

            // $this->selfActiveTimer = ProjectTimeLog::selfActiveTimer();
            // $clockHtml = view('sections.timer_clock', $this->data)->render();

        


            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => 'Monthly task and subtask created successfully.',
                'task_id' => $task->id,
                'timer_id' => $timeLog->id,
            ]);
        } catch (\Exception $e) {
            dd($e->getMessage());
            DB::rollback();
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to create monthly task and subtask: ' . $e->getMessage(),
            ]);
        }
    }

     public function getTaskShortCode($projectShortCode, $lastProjectCount)
    {
        $task = Task::where('task_short_code', $projectShortCode . '-' . $lastProjectCount)->exists();

        if ($task) {
            return $this->getTaskShortCode($projectShortCode, $lastProjectCount + 1);
        }

        return $lastProjectCount;

    }

    public function pauseTimer(Request $request)
    {
        
        $user = auth()->user();
        $timeId = $request->timer_id;

        $timeLog = ProjectTimeLog::findOrFail($timeId);

        // Start a break (or continue an existing break)
        $timeLogBreak = ProjectTimeLogBreak::where('project_time_log_id', $timeLog->id)
            ->whereNull('end_time')->first() ?: new ProjectTimeLogBreak();

        $timeLogBreak->project_time_log_id = $timeLog->id;
        $timeLogBreak->start_time = now();
        $timeLogBreak->total_minutes = 0;
        $timeLogBreak->save();

        // Optional activity logging (if methods exist)
        if (method_exists($this, 'logProjectActivity') && $timeLog->project_id) {
            $this->logProjectActivity($timeLog->project_id, 'modules.tasks.timerPausedBy');
        }

        if (method_exists($this, 'logTaskActivity') && $timeLog->task_id) {
            $this->logTaskActivity($timeLog->task_id, $user->id, 'timerPausedBy');
        }

        if (method_exists($this, 'logUserActivity')) {
            $this->logUserActivity($user->id, 'modules.tasks.timerPausedBy');
        }
        return response()->json([
            'status' => 'success',
            'message' => __('messages.timerPausedSuccessfully'),
            'timer_id' => $timeLog->id,
            'break_id' => $timeLogBreak->id,
        ]);
    }

    public function resumeTimer(Request $request)
    {
        try{
            DB::beginTransaction();
            $user = auth()->user();
            $breakId = $request->break_id;
            $timeId = $request->timer_id;
            // Find the break and the parent time log
            $timeLogBreak = ProjectTimeLogBreak::findOrFail($breakId);
            $timeLog = ProjectTimeLog::findOrFail($timeId);

            // Check if another timer is active
            $activeTimer = ProjectTimeLog::selfActiveTimer();
            $totalActiveTimers = ProjectTimeLog::totalActiveTimer();

            $activeBreaks = 0;
            if (count($totalActiveTimers) > 1) {
                foreach ($totalActiveTimers as $t) {
                    $activeBreaks += $t->activeBreak ? 1 : 0;
                }
                if ($activeBreaks != count($totalActiveTimers)) {
                    return response()->json([
                        'status' => 'error',
                        'message' => __('messages.timerAlreadyRunning'),
                    ]);
                }
            }

            // Resume the timer only if no active timer or current timer is on break
            if (is_null($activeTimer) || (!is_null($activeTimer) && !is_null($activeTimer->activeBreak))) {
                $endTime = now();

                // Close the break
                $timeLogBreak->end_time = $endTime;
                $timeLogBreak->total_hours = $endTime->diffInHours($timeLogBreak->start_time);
                $timeLogBreak->total_minutes = $endTime->diffInMinutes($timeLogBreak->start_time);
                $timeLogBreak->save();

                // Optional logging
                if (method_exists($this, 'logTaskActivity') && $timeLog->task_id) {
                    $this->logTaskActivity($timeLog->task_id, $user->id, 'timerResumedBy');
                }
                if (method_exists($this, 'logUserActivity')) {
                    $this->logUserActivity($user->id, 'modules.tasks.timerStartedBy');
                }
                DB::commit();
                return response()->json([
                    'status' => 'success',
                    'message' => __('messages.timerStartedSuccessfully'),
                    'timer_id' => $timeLog->id,
                ]);
            }
            DB::commit();
            return response()->json([
                'status' => 'error',
                'message' => __('messages.timerAlreadyRunning'),
            ]);
        }catch (\Exception $e) {
            DB::rollback();
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to resume timer: ' . $e->getMessage(),
            ]);
        }
    }

    public function stopTimer(Request $request)
    {
        $user = auth()->user();
        $timeId = $request->timer_id;

        $timeLog = ProjectTimeLog::with('activeBreak', 'project')->findOrFail($timeId);

        // Stop timer
        $timeLog->end_time = now();
        $timeLog->save();

        $timeLog->total_hours = $timeLog->end_time->diffInHours($timeLog->start_time);
        $timeLog->total_minutes = $timeLog->end_time->diffInMinutes($timeLog->start_time);
        $timeLog->edited_by_user = $user->id;
        $timeLog->memo = $request->memo;
        $timeLog->save();

        // If break is active, stop it
        if ($timeLog->activeBreak) {
            $activeBreak = $timeLog->activeBreak;
            $activeBreak->end_time = $timeLog->end_time;
            $activeBreak->total_minutes = $timeLog->end_time->diffInMinutes($activeBreak->start_time);
            $activeBreak->total_hours = $timeLog->end_time->diffInHours($activeBreak->start_time);
            $activeBreak->save();
        }

        // Optional activity logs (only if methods available)
        if (method_exists($this, 'logProjectActivity') && $timeLog->project_id) {
            $this->logProjectActivity($timeLog->project_id, 'modules.tasks.timerStoppedBy');
        }

        if (method_exists($this, 'logTaskActivity') && $timeLog->task_id) {
            $this->logTaskActivity($timeLog->task_id, $user->id, 'timerStoppedBy');
        }

        if (method_exists($this, 'logUserActivity')) {
            $this->logUserActivity($user->id, 'modules.tasks.timerStoppedBy');
        }

        // Get count of remaining active timers
        $timerCountQuery = ProjectTimeLog::whereNull('end_time')
            ->join('users', 'users.id', '=', 'project_time_logs.user_id')
            ->select('project_time_logs.id');


        // Get current active timer (if any)
        $selfActiveTimer = ProjectTimeLog::doesnthave('activeBreak')
            ->where('user_id', $user->id)
            ->whereNull('end_time')
            ->first();

        return response()->json([
            'status' => 'success',
            'message' => __('messages.timerStoppedSuccessfully'),
            'timer_id' => $timeLog->id,
        ]);
    }
    
    public function screenshot(Request $request)
    {
        $user = auth()->user();
        $timeId = $request->timer_id;

        return response()->json([
            'status' => 'success',
            'message' => __('Picture added successfully'),
        ]);
    }


}
