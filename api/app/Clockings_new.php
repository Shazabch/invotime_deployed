<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Clockings_new extends Model
{
    protected $fillable = [
        'device_id','no','employee_id','name','mode','type','datetime','action','shift_id','weather'
    ];
}
