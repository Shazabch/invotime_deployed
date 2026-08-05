<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Branch extends Model
{
    public function employees()
    {
       return $this->hasMany(Employee::class);
    }
     public function devices()
    {
       return $this->hasMany(Devices::class);
    }
      public function company()
    {
        return $this->belongsTo(Company::class);
    }
}
