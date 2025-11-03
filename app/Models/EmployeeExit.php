<?php

// app/Models/EmployeeExit.php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EmployeeExit extends Model
{
    protected $table = 'employee_exits';

    protected $fillable = [
        'employee_id',
        'notice_date',
        'effective_date',
        'kind',             // 'termination' | 'resignation'
        'termination_type', // nullable if resignation
        'description',
        'created_by',
    ];

    public const KIND_TERMINATION = 'termination';
    public const KIND_RESIGNATION = 'resignation';

    // static types (you can rename labels)
    public const TERMINATION_TYPES = [
        1 => 'Performance',
        2 => 'Misconduct',
        3 => 'Redundancy',
    ];

    public function employee()
    {
        // points to users.id (INT UNSIGNED)
        return $this->belongsTo(User::class, 'employee_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function getTerminationTypeLabelAttribute(): ?string
    {
        return $this->termination_type
            ? (self::TERMINATION_TYPES[$this->termination_type] ?? (string) $this->termination_type)
            : null;
    }
}
