<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MqttConfig extends Model
{
    use HasFactory;

    protected $fillable = [
        'host',
        'port',
        'username',
        'password',
        'client_id',
        'is_active'
    ];

    protected $casts = [
        'port' => 'integer',
        'is_active' => 'boolean'
    ];

    protected $hidden = [
        'password'
    ];
}
