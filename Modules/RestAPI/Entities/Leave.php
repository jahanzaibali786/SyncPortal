<?php

namespace Modules\RestAPI\Entities;

use App\Observers\LeaveObserver;
use Carbon\Carbon;

class Leave extends \App\Models\Leave
{
    // region Properties

    protected $table = 'leaves';

    protected $default = [
        'id',
        'leave_type_id',
        'leave_date',
        'reason',
        'status',
    ];

    protected $hidden = [
        'leave.leave_type_id',
    ];

    // REMOVE the rigid dates array to avoid forcing createFromFormat in some packages
    // protected $dates = [
    //     'leave_date',
    // ];

    protected $guarded = [
        'id',
    ];

    protected $filterable = [
        'id',
        'status',
        'user_id',
        'leave_type_id',
        'employee_name',
    ];
    protected $casts = [
        'leave_date' => 'datetime',
    ];

    public static function boot()
    {
        parent::boot();
        static::observe(LeaveObserver::class);
    }

    public function visibleTo(\App\Models\User $user)
    {
        if ($user->hasRole('admin') || $user->hasRole('employee') || $user->cans('view_leave')) {
            return true;
        }

        return false;
    }

    public function scopeVisibility($query)
    {
        if (api_user()) {
            $user = api_user();

            if ($user->hasRole('admin')) {
                return $query;
            }

            if ($user->hasRole('employee')) {
                $query->where('user_id', $user->id);

                return $query;
            }
        }
    }

    /**
     * Defensive accessor for leave_date.
     * Always try to parse flexibly and return a normalized string for API consumers.
     *
     * @param  mixed $value
     * @return string|null
     */
   public function getLeaveDateAttribute($value)
    {
        return $value ? Carbon::parse($value) : null;
    }


    /**
     * Mutator to normalize leave_date before saving to DB.
     * Accepts flexible inputs and converts to 'Y-m-d H:i:s' or 'Y-m-d' depending on presence of time.
     *
     * @param mixed $value
     * @return void
     */
    public function setLeaveDateAttribute($value)
    {
        if ($value === null || $value === '') {
            $this->attributes['leave_date'] = null;
            return;
        }

        // If caller passed Carbon or DateTime, format accordingly
        if ($value instanceof Carbon) {
            $this->attributes['leave_date'] = $value->format('Y-m-d H:i:s');
            return;
        }
        if ($value instanceof \DateTime) {
            $this->attributes['leave_date'] = Carbon::instance($value)->format('Y-m-d H:i:s');
            return;
        }

        $raw = trim((string)$value);

        // Try to parse flexibly and store normalized
        try {
            $c = Carbon::parse($raw);
            // If raw looks like date-only, store date-only (optional - choose one)
            if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $raw)) {
                $this->attributes['leave_date'] = $c->toDateString();
            } else {
                $this->attributes['leave_date'] = $c->toDateTimeString();
            }
            return;
        } catch (\Exception $e) {
            // Fallback: try a relaxed normalization
            $normalized = preg_replace([
                '/^"(.*)"$/', '/,\s*$/', '/T/', '/Z$/', '/([+-][0-9]{2}(:?[0-9]{2})?)$/', '/\.[0-9]{3,6}$/'
            ], [
                '$1', '', ' ', '', '', ''
            ], $raw);

            $normalized = trim($normalized);

            try {
                $c = Carbon::parse($normalized);
                if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $normalized)) {
                    $this->attributes['leave_date'] = $c->toDateString();
                } else {
                    $this->attributes['leave_date'] = $c->toDateTimeString();
                }
                return;
            } catch (\Exception $e2) {
                // As ultimate fallback, store raw value so DB retains original for inspection
                $this->attributes['leave_date'] = $raw;
            }
        }
    }
}
