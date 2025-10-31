<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class RoleNotificationSetting extends Model 
{
    protected $fillable = ['role_id','notification_event_id','enabled','channels'];
    protected $casts = ['channels' => 'array','enabled'=>'boolean'];
    public function event(){ return $this->belongsTo(NotificationEvent::class, 'notification_event_id'); }
}
