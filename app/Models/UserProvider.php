<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserProvider extends Model
{
    use HasFactory;

    protected $fillable = [
        'provider_id',
        'name',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
