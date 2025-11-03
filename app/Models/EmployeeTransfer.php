<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EmployeeTransfer extends Model
{
    protected $fillable = [
        'employee_id',
        'from_department_id',
        'to_department_id',
        'transfer_date',
        'description',
        'created_by',
    ];

    protected $casts = [
        'transfer_date' => 'date',
    ];

    public function employee()       { return $this->belongsTo(\App\Models\User::class, 'employee_id'); }
    public function fromDepartment() { return $this->belongsTo(\App\Models\Team::class,  'from_department_id'); }
    public function toDepartment()   { return $this->belongsTo(\App\Models\Team::class,  'to_department_id'); }
    public function creator()        { return $this->belongsTo(\App\Models\User::class, 'created_by'); }
}
