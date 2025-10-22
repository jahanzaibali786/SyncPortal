<?php

namespace App\Http\Controllers;

use App\Models\LeadPipeline;
use App\Models\PipelineLabel;
use Illuminate\Http\Request;
use App\Models\Lead;
use App\Models\Deal;


class PipelineLabelController extends Controller
{
    public function index()
    {
        $labels = PipelineLabel::with('pipeline')->get();

        $view = view('lead-settings.ajax.labels', compact('labels'))->render();

        return response()->json([
            'status' => 'success',
            'html' => $view,
        ]);
    }

    public function create(Request $request)
    {
        if (isset($request->deal_id) && !empty($request->deal_id)) {
            $deal = Deal::with('dealLabels')->findOrFail($request->deal_id);
            $pipelines = LeadPipeline::where('id', $deal->lead_pipeline_id)->get();
            return view('lead-settings.create-label-modal', compact('pipelines', 'deal'));
        } else {
            $pipelines = LeadPipeline::all();
            return view('lead-settings.create-label-modal', compact('pipelines'));
        }
    }

    public function store(Request $request)
    {
        $request->validate([
            'lead_pipeline_id' => 'required|exists:lead_pipelines,id',
            'name' => 'required|string|max:255',
            'label_color' => 'nullable|string|max:20',
        ]);

        $label = PipelineLabel::create([
            'pipeline_id' => $request->lead_pipeline_id,
            'name' => $request->name,
            'label_color' => $request->label_color ?? '#009688', // default color
            'added_by' => auth()->id() ?? null, // null if no user is logged in
        ]);

        // return response()->json(['status' => 'success']);
        return response()->json([
            'status' => 'success',
            'label' => [
                'id' => $label->id,
                'name' => $label->name,
                'label_color' => $label->label_color,
            ],
        ]);
    }



    public function edit($id)
    {
        $label = PipelineLabel::findOrFail($id);
        $pipelines = LeadPipeline::all();
        return view('lead-settings.edit-label-modal', compact('label', 'pipelines'));
    }

    public function update(Request $request, $id)
    {
        $label = PipelineLabel::findOrFail($id);

        $request->validate([
            'lead_pipeline_id' => 'required|exists:lead_pipelines,id',
            'name' => 'required|string|max:255',
        ]);

        $label->update($request->only('lead_pipeline_id', 'name', 'color'));

        return response()->json(['status' => 'success']);
    }

    public function destroy($id)
    {
        PipelineLabel::findOrFail($id)->delete();
        return response()->json(['status' => 'success']);
    }


    public function getLeadLabels(Request $request)
    {
        $lead = Lead::with('labels')->findOrFail($request->lead_id);

        return response()->json([
            'status' => 'success',
            'label_ids' => $lead->labels->pluck('id')
        ]);
    }

    public function updateLeadLabels(Request $request)
    {
        $request->validate([
            'lead_id' => 'required|exists:leads,id',
            'labels' => 'array'
        ]);

        // 👇 Temporary debug
        // dd($request->all());

        $lead = Lead::findOrFail($request->lead_id);
        $lead->dealLabels()->sync($request->labels ?? []);

        return response()->json(['status' => 'success']);
    }


    // ✅ Get labels for a specific deal
    public function getDealLabels(Request $request)
    {
        $deal = Deal::with('dealLabels')->findOrFail($request->deal_id);

        return response()->json([
            'status' => 'success',
            'label_ids' => $deal->labels->pluck('id')
        ]);
    }

    // ✅ Update labels for a specific deal
    public function updateDealLabels(Request $request)
    {
        $request->validate([
            'deal_id' => 'required|exists:deals,id',
            'labels' => 'array'
        ]);

        $deal = Deal::findOrFail($request->deal_id);
        $deal->dealLabels()->sync($request->labels ?? []);

        return response()->json([
            'status' => 'success',
            'labels' => $deal->dealLabels->map(function ($label) {
                return [
                    'id' => $label->id,
                    'name' => $label->name,
                    'label_color' => $label->label_color
                ];
            })
        ]);

    }


}
