<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PipelineLabel extends Model
{
    use HasFactory;

    protected $fillable = [
        'pipeline_id',
        'name',
        'label_color',
        'added_by'
    ];

    // Relationship to Pipeline
    public function pipeline()
    {
        return $this->belongsTo(\App\Models\LeadPipeline::class, 'pipeline_id');
    }

    // Relationship to User
    public function addedBy()
    {
        return $this->belongsTo(\App\Models\User::class, 'added_by');
    }

    public function leads()
    {
        return $this->belongsToMany(\App\Models\Lead::class, 'lead_label', 'label_id', 'lead_id')
            ->withTimestamps();
    }

    public function deals()
    {
        return $this->belongsToMany(Deal::class, 'deal_label', 'label_id', 'deal_id');
    }


}
