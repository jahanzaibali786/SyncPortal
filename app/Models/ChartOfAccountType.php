<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ChartOfAccountType extends Model
{
    use HasFactory;
    protected $fillable = ['company_id','code','name'];
    public function subtypes(){ return $this->hasMany(ChartOfAccountSubType::class); }
    public function accounts(){ return $this->hasMany(ChartOfAccount::class); }
    
}
