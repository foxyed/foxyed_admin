<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Course extends Model
{
    protected $fillable = [
        'course_category_id',
        'title',
        'code',
        'description',
        'price',
        'active',
    ];

    protected $casts = [
        'active' => 'boolean',
        'price' => 'decimal:2',
    ];

    public function category()
    {
        return $this->belongsTo(CourseCategory::class, 'course_category_id');
    }

    public function teachers()
    {
        return $this->belongsToMany(User::class, 'course_user')
            ->withPivot(['role'])
            ->withTimestamps();
    }

    public function books()
    {
        return $this->hasMany(Book::class);
    }
}
