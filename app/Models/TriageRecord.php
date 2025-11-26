<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TriageRecord extends Model
{
    protected $fillable = [
        'user_id',
        'symptoms',
        'analysis',
        'severity',
    ];
}
