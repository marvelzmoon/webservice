<?php

namespace App\Models\Khanza;

use Illuminate\Database\Eloquent\Model;

class TagihanSadewaModel extends Model
{
    protected $connection = "second_db";
    protected $table = "tagihan_sadewa";
    public $incrementing = false;
    protected $keyType = 'string';
    public function MapPaymentKhanza()
    {
        return $this->hasOne('App\Models\MapPaymentKhanza');
    }
}
