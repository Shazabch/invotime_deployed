<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Devices extends Model
{
     public function branch()
    {
        return $this->belongsTo(Branch::class);
    }
}
