<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class IoDashboardActive extends Model
{
    public $timestamps = false;
    protected $table = "io_dashboard_active";
    protected $fillable = [
        'dashac_tgl',
        'dashac_idddash',
        'dashac_status',
    ];
}
