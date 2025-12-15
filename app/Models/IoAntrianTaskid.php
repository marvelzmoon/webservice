<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class IoAntrianTaskid extends Model
{
    public $timestamps = false;
    protected $table = "io_antrian_taskid";
    public $incrementing = false;
    protected $keyType = 'string';
    protected $primaryKey = 'nobooking';
    protected $fillable = [
        'nobooking',
        'taskid_3',
        'taskid_3_send',
        'taskid_4',
        'taskid_4_send',
        'taskid_5',
        'taskid_5_send',
        'taskid_6',
        'taskid_6_send',
        'taskid_7',
        'taskid_7_send',
        'taskid_99',
        'taskid_99_send'
    ];
}
