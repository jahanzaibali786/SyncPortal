<?php

namespace App\Http\Controllers;

use App\Models\Deal;
use App\Models\LeadCall;
use Illuminate\Http\Request;
use App\Models\Lead;
use App\Models\LeadActivityLog;

use Illuminate\Support\Facades\Auth;

class LeadCallController extends Controller
{
    // Lead Calls
    public function index()
    {
        $calls = LeadCall::with(['lead', 'user'])->latest()->get();
        // dd($calls);
        return response()->json($calls);
    }
    public function callCreate($id)
    {
        if (\Auth::user()->can('create lead call')) {
            $lead = Lead::find($id);
            if ($lead->created_by == \Auth::user()->creatorId()) {
                $users = UserLead::where('lead_id', '=', $lead->id)->get();

                return view('leads.calls', compact('lead', 'users'));
            } else {
                return response()->json(
                    [
                        'is_success' => false,
                        'error' => __('Permission Denied.'),
                    ],
                    401
                );
            }
        } else {
            return response()->json(
                [
                    'is_success' => false,
                    'error' => __('Permission Denied.'),
                ],
                401
            );
        }
    }

    public function callStore($id, Request $request)
    {
        if (\Auth::user()->can('create lead call')) {
            $usr = \Auth::user();
            $lead = Lead::find($id);
            if ($lead->created_by == \Auth::user()->creatorId()) {
                $validator = \Validator::make(
                    $request->all(),
                    [
                        'subject' => 'required',
                        'call_type' => 'required',
                        'user_id' => 'required',
                    ]
                );

                if ($validator->fails()) {
                    $messages = $validator->getMessageBag();

                    return redirect()->back()->with('error', $messages->first());
                }

                $leadCall = LeadCall::create(
                    [
                        'lead_id' => $lead->id,
                        'subject' => $request->subject,
                        'call_type' => $request->call_type,
                        'duration' => $request->duration,
                        'user_id' => $request->user_id,
                        'description' => $request->description,
                        'call_result' => $request->call_result,
                    ]
                );

                LeadActivityLog::create(
                    [
                        'user_id' => $usr->id,
                        'lead_id' => $lead->id,
                        'log_type' => 'Create lead call',
                        'remark' => json_encode(['title' => 'Create new Lead Call']),
                    ]
                );

                $leadArr = [
                    'lead_id' => $lead->id,
                    'name' => $lead->name,
                    'updated_by' => $usr->id,
                ];

                $call = $leadCall;
                $html = view('leads.callstr', compact('lead', 'call'))->render();

                $data = [
                    'datarow' => $html,
                    'table_id' => "callstable",
                    'action' => 'add',
                    'row_id' => $lead->id,
                ];

                return response()->json(['success' => true, 'message' => __('Call successfully created!'), "data" => $data]);
            } else {
                return response()->json(['error' => __('Permission Denied.')]);
            }
        } else {
            return response()->json(['error' => __('Permission Denied.')]);
        }
    }

    public function callActivity(Request $request)
    {
        LeadActivityLog::create(
            [
                'user_id' => $request->user,
                'lead_id' => $request->lead,
                'log_type' => 'Call Made',
                'remark' => json_encode(['title' => 'Call To User']),
            ]
        );
        return response()->json([
            'is_success' => true,
            'message' => 'Call activity logged successfully.',
        ]);
    }

    public function callEdit($id, $call_id)
    {
        if (\Auth::user()->can('edit lead call')) {
            $lead = Lead::find($id);
            if ($lead->created_by == \Auth::user()->creatorId()) {
                $call = LeadCall::find($call_id);
                $users = UserLead::where('lead_id', '=', $lead->id)->get();

                return view('leads.calls', compact('call', 'lead', 'users'));
            } else {
                return response()->json(
                    [
                        'is_success' => false,
                        'error' => __('Permission Denied.'),
                    ],
                    401
                );
            }
        } else {
            return response()->json(
                [
                    'is_success' => false,
                    'error' => __('Permission Denied.'),
                ],
                401
            );
        }
    }

    public function callUpdate($id, $call_id, Request $request)
    {
        if (\Auth::user()->can('edit lead call')) {
            $lead = Lead::find($id);
            if ($lead->created_by == \Auth::user()->creatorId()) {
                $validator = \Validator::make(
                    $request->all(),
                    [
                        'subject' => 'required',
                        'call_type' => 'required',
                        'user_id' => 'required',
                    ]
                );

                if ($validator->fails()) {
                    $messages = $validator->getMessageBag();

                    return redirect()->back()->with('error', $messages->first());
                }

                $call = LeadCall::find($call_id);

                $call->update(
                    [
                        'subject' => $request->subject,
                        'call_type' => $request->call_type,
                        'duration' => $request->duration,
                        'user_id' => $request->user_id,
                        'description' => $request->description,
                        'call_result' => $request->call_result,
                    ]
                );

                LeadActivityLog::create(
                    [
                        'user_id' => \Auth::user()->id,
                        'lead_id' => $lead->id,
                        'log_type' => 'update lead call',
                        'remark' => json_encode(['title' => 'Update Lead Call']),
                    ]
                );

                $html = view('leads.callstr', compact('lead', 'call'))->render();

                $data = [
                    'datarow' => $html,
                    'table_id' => "callstable",
                    'action' => 'edit',
                    'row_id' => $lead->id,
                ];

                return response()->json(['success' => true, 'message' => __('Call successfully Updated!'), "data" => $data]);
            } else {
                return response()->json(['error' => __('Permission Denied.')]);
            }
        } else {
            return response()->json(['error' => __('Permission Denied.')]);
        }
    }

    public function callDestroy($id, $call_id)
    {
        if (\Auth::user()->can('delete lead call')) {
            $lead = Lead::find($id);
            if ($lead->created_by == \Auth::user()->creatorId()) {
                $task = LeadCall::find($call_id);
                $task->delete();
                LeadActivityLog::create(
                    [
                        'user_id' => \Auth::user()->id,
                        'lead_id' => $lead->id,
                        'log_type' => 'delete lead call',
                        'remark' => json_encode(['title' => 'Delete Lead Call']),
                    ]
                );

                return redirect()->back()->with('success', __('Call successfully deleted!'))->with('status', 'calls');
            } else {
                return redirect()->back()->with('error', __('Permission Denied.'))->with('status', 'calls');
            }
        } else {
            return redirect()->back()->with('error', __('Permission Denied.'))->with('status', 'calls');
        }
    }


    /**
     * Open Create Call Modal
     */
    public function callCreateModal(Request $request)
    {
        $leadId = $request->get('lead_id');
        // dd($leadId);
        $lead = Deal::findOrFail($leadId);
        return view('leads.ajax.call_create', compact('lead'));
    }

    /**
     * Store Call from Modal
     */
    // public function callStoreModal(Request $request)
    // {
    //     $validatedData = $request->validate([
    //         'lead_id' => ['required', 'exists:leads,id'],
    //         'subject' => ['required', 'string', 'max:191'],
    //         'call_type' => ['required', 'string', 'max:30'],
    //         'to_number' => ['nullable', 'string', 'max:15', 'regex:/^[0-9+\-\s()]*$/'],
    //         'start' => ['required', 'date'], // changed to date validation
    //         'end' => ['required', 'date', 'after_or_equal:start'], // must be >= start
    //         'status' => ['nullable', 'string', 'max:25'],
    //         'description' => ['nullable', 'string'],
    //         'call_result' => ['nullable', 'string'],
    //     ]);

    //     // Convert start & end to Carbon
    //     $start = \Carbon\Carbon::parse($validatedData['start']);
    //     $end = \Carbon\Carbon::parse($validatedData['end']);

    //     // Calculate duration (in seconds)
    //     $durationSeconds = $end->diffInSeconds($start);

    //     // Format duration as H:i:s
    //     $validatedData['duration'] = gmdate('H:i:s', $durationSeconds);

    //     // Set default values
    //     $validatedData['status'] = $validatedData['status'] ?? 'answer';
    //     $validatedData['user_id'] = Auth::id();

    //     // Store timestamps as strings (varchar)
    //     $validatedData['start'] = $start->format('Y-m-d H:i:s');
    //     $validatedData['end'] = $end->format('Y-m-d H:i:s');

    //     // Create record
    //     $call = LeadCall::create($validatedData);

    //     // Log call history
    //     \App\Traits\DealHistoryTrait::createDealHistory(
    //         $call->lead_id,
    //         'call-created',
    //         callId: $call->id,
    //     );

    //     return response()->json([
    //         'status' => 'success',
    //         'message' => __('Call added successfully.'),
    //         'data' => $call,
    //     ], 201);
    // }

    public function callStoreModal(Request $request)
    {
        // dd($request->lead_id);
        $validatedData = $request->validate([
            'lead_id' => ['required', 'exists:deals,id'],
            'subject' => ['required', 'string', 'max:191'],
            'call_type' => ['required', 'string', 'max:30'],
            'to_number' => ['nullable', 'string', 'max:15', 'regex:/^[0-9+\-\s()]*$/'],
            'call_date' => ['required', 'date'],
            'start_time' => ['required', 'date_format:H:i'],
            'end_time' => ['required', 'date_format:H:i', 'after:start_time'],
            'status' => ['nullable', 'string', 'max:25'],
            'description' => ['nullable', 'string'],
            'call_result' => ['nullable', 'string'],
        ]);


        // Combine date and times into full timestamps
        $start = \Carbon\Carbon::parse($validatedData['call_date'] . ' ' . $validatedData['start_time']);
        $end = \Carbon\Carbon::parse($validatedData['call_date'] . ' ' . $validatedData['end_time']);

        // Calculate duration
        $durationSeconds = $end->diffInSeconds($start);
        $validatedData['duration'] = gmdate('H:i:s', $durationSeconds);

        // Store as varchar (Y-m-d H:i:s)
        $validatedData['start'] = $start->format('Y-m-d H:i:s');
        $validatedData['end'] = $end->format('Y-m-d H:i:s');
        $validatedData['user_id'] = Auth::id();
        $validatedData['status'] = $validatedData['status'] ?? 'answer';

        // Create call record
        $call = LeadCall::create($validatedData);

        // Log call history
        \App\Traits\DealHistoryTrait::createDealHistory(
            $call->lead_id,
            'call-created',
            callId: $call->id,
        );

        return response()->json([
            'status' => 'success',
            'message' => __('Call added successfully.'),
            'data' => $call,
        ], 201);
    }



}
