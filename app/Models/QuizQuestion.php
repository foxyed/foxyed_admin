<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class QuizQuestion extends Model
{
    protected $fillable = [
        'quiz_id',
        'order',
        'type',
        'prompt',
        'data',
        'explanation',
        'points',
    ];

    protected $casts = [
        'data' => 'array',
        'points' => 'decimal:2',
    ];

    public function quiz()
    {
        return $this->belongsTo(Quiz::class);
    }
}
