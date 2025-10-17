<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GoogleMeetings extends Model
{
    use HasFactory;
    protected $fillable = [
        'id',
        'lead_id',
        'title',
        'start',
        'end',
        'description',
        'assigned_to',
        'google_meet_link',
        'google_meet_id',
        'google_meet_password',
    ];

    //assigned to in array 
    public function users()
    {
        return $this->belongsToMany('App\Models\User', 'google_meetings', 'assigned_to', 'id');
    }
}
