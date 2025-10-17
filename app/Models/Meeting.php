<?php  
  
namespace App\Models;  
  
use Illuminate\Database\Eloquent\Factories\HasFactory;  
use Illuminate\Database\Eloquent\Model;  
  
class Meeting extends Model  
{  
    use HasFactory;  
  
    // protected $fillable = [  
    //     'lead_id',  
    //     'user_id',  
    //     'meeting_date',  
    //     'meeting_time',  
    //     'meeting_minutes',  
    //     'join_url',  
    // ];  
  
    public function lead()  
    {  
        return $this->belongsTo(Lead::class);  
    }  
  
    public function user()  
    {  
        return $this->belongsTo(User::class);  
    }  
}