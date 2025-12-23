<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SatuSehatEncounter extends Model
{
    public $timestamps = false;
    protected $table = "satu_sehat_encounter";
    public $incrementing = false;
    protected $keyType = 'string';
    protected $primaryKey = 'no_rawat';

    protected $fillable = [
        'no_rawat',
        'id_encounter',
    ];
}
