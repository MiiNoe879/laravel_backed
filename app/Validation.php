<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Validation extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
     protected $fillable = [
        'ip_address', 'phone_number', 'pin_code'
    ];
}
