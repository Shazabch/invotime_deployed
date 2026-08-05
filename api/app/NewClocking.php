<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class NewClocking extends Model
{
    protected $fillable = [
        'id', 'employee_id', 'device_id', 'shift_id', 'clock_in', 'clock_out', 'clock_in_id', 'clock_out_id', 'reason', 'remark', 'scan_type_in', 'scan_type_out', 'created_at', 'updated_at'
    ];
}
