<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Session extends Model
{
    protected $primaryKey = 'id';
    protected $keyType = 'string';

    public $timestamps = false;
    public $incrementing = false;

    protected $casts = [
        'payload' => 'string',
        'last_activity' => 'integer',
    ];

    protected $hidden = [
        'payload',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
