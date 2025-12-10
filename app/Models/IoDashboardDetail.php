<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class IoDashboardDetail extends Model
{
    public $timestamps = false;
    protected $table = "io_dashboard_detail";
    public $incrementing = true;
    protected $keyType = 'int';
    protected $primaryKey = 'ddash_id';
    protected $fillable = [
        'ddash_parent',
        'ddash_poli',
        'ddash_dokter',
        'dashd_status'
    ];
}
