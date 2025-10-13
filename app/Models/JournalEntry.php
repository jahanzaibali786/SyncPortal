<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class JournalEntry extends Model
{
    const FILE_PATH = 'vouchers';
    protected $fillable = [
        'company_id',
        'date',
        'number',
        'memo',
        'bank_id',
        'voucher_type',
        'source_type',
        'payment_method',
        "check_number",
        "bank_reference",
        "deposit_slip",
        "cashier_info",
        'tax_id',
        'source_id',
        'currency',
        'posted_at',
        'voided_at',
        'created_by',
        'approved_by'
    ];

    public function lines()
    {
        return $this->hasMany(JournalEntryLine::class);
    }

    public function source()
    {
        return $this->morphTo();
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }
    // public function fiscalPeriod()
    // {
    //     return $this->belongsTo(FiscalPeriod::class);
    // }
}
