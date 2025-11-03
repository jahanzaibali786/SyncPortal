<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NotificationEvent extends Model 
{
    protected $fillable = ['key','label','module','default_channels'];
    protected $casts = ['default_channels' => 'array'];
}
