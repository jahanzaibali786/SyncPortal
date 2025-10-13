<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ChartOfAccountSubType extends Model
{
    use HasFactory;
    protected $fillable = ['company_id','chart_of_account_type_id','code','name'];
    public function type(){ return $this->belongsTo(ChartOfAccountType::class,'chart_of_account_type_id'); }
    public function accounts(){ return $this->hasMany(ChartOfAccount::class); }
}
