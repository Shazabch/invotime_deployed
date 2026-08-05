<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Clockings extends Model
{
    protected $fillable = [
        'employee_id','clock_date','clock_in','clock_out','device_id','scan_type_in','scan_type_out','weather','shift_id'
    ];
}
