<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class IoUser extends Model
{
    protected $table = 'io_user';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'id',
        'password',
        'nip',
        'api_token',
        'api_token_expires_at'
    ];

    protected $hidden = [
        'password',
        'api_token'
    ];

    public $timestamps = false;
}
