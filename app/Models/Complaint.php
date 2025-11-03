<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Complaint extends Model
{
    protected $fillable = [
        'complaint_from',
        'complaint_against',
        'title',
        'complaint_date',
        'description',
        'created_by',
    ];

    protected $casts = [
        'complaint_date' => 'date',
    ];

    public function from()
    {
        return $this->belongsTo(User::class, 'complaint_from');
    }

    public function against()
    {
        return $this->belongsTo(User::class, 'complaint_against');
    }
}
