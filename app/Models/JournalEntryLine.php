<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class JournalEntryLine extends Model
{
    protected $fillable = [
        'journal_entry_id','company_id','chart_of_account_id','debit','credit','tax_id'
        ,'cost_center_id','project_id','memo','source_line_type','source_line_id'
    ];

    public function journalEntry()
    {
        return $this->belongsTo(JournalEntry::class);
    }

    public function chartOfAccount()
    {
        return $this->belongsTo(ChartOfAccount::class);
    }

    public function tax()
    {
        return $this->belongsTo(Tax::class);
    }

    public function sourceLine()
    {
        return $this->morphTo();
    }
}
