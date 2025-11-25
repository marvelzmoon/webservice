<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class IoSetting extends Model
{
    public $timestamps = false;
    protected $table = "io_settings";
    public $incrementing = false;
    protected $keyType = 'string';
    protected $primaryKey = 'setting_option';
    protected $fillable = [
        'setting_option',
        'group',
        'value',
    ];
}
