<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Invite extends Model
{
    protected $fillable = [
        'id', 'meeting_id', 'creator_email', 'invitee_email', 'status'
    ];
}
