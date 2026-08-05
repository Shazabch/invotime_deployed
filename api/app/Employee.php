<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Employee extends Model
{
    protected $fillable = [
        'email', 'password','first_name','api_token','fcm_token'
    ];

   public function company()
    {
        return $this->belongsTo(Company::class);
    }
    public function position()
    {
        return $this->belongsTo(Position::class);
    }
     public function department()
    {
        return $this->belongsTo(Department::class);
    }
    
    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function role()
    {
        return $this->belongsTo(Role::class);
    }
}
