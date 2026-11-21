<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TriageRecord extends Model
{
    protected $fillable = [
        'user_id',
        'symptoms',
        'patient',
        'analysis',
        'severity',
        'advice',
        'reason',
        'details',
    ];

    protected function casts(): array
    {
        return [
            'patient' => 'array',
            'details' => 'array',
        ];
    }
}
