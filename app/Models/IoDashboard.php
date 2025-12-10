<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class IoDashboard extends Model
{
    public $timestamps = false;
    protected $table = "io_dashboard";
    public $incrementing = true;
    protected $keyType = 'int';
    protected $primaryKey = 'dash_id';
    protected $fillable = [
        'dash_name',
        'dash_status'
    ];
}
