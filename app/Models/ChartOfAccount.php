<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ChartOfAccount extends Model
{
    use HasFactory;
    protected $fillable = ['company_id','code','name','chart_of_account_type_id','chart_of_account_sub_type_id','parent_id','description','is_active'];
    public function parent(){ return $this->belongsTo(ChartOfAccount::class, 'parent_id'); }
    public function children(){ return $this->hasMany(ChartOfAccount::class, 'parent_id'); }
    // public function lines(){ return $this->hasMany(JournalEntryLine::class); }
    public function scopeCompany($q, $companyId){ return $q->where('company_id', $companyId); }
    public function accountType()
    {
        return $this->belongsTo(ChartOfAccountType::class, 'chart_of_account_type_id');
    }
    public function accountSubType()
    {
        return $this->belongsTo(ChartOfAccountSubType::class, 'chart_of_account_sub_type_id');
    }
}
