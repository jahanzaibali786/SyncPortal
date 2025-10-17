<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GoogleCalanderAccounts extends Model
{
    use HasFactory;
    protected $fillable = [
        'user_id',
        'google_account_id',
        'access_token',
        'refresh_token',
        'expires_at',
    ];
}
