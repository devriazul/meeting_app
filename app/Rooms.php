<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Rooms extends Model
{
    protected $fillable = [
        'id', 'room', 'month', 'day', 'hour', 'minute', 'hour_finish', 'minute_finish'
    ];

    protected $hidden = [
        'email'
    ];
}
