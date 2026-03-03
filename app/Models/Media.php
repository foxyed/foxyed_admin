<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Media extends Model
{
    protected $table = 'media';

    protected $fillable = [
        'user_id',
        'course_id',
        'disk',
        'path',
        'original_name',
        'mime',
        'size',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function course()
    {
        return $this->belongsTo(Course::class);
    }

    public function url(): string
    {
        return \Storage::disk($this->disk)->url($this->path);
    }
}
