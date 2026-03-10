<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Chapter extends Model
{
    protected $fillable = [
        'book_id',
        'title',
        'order',
        'content_json',
        'content_html',
        'status',
    ];

    public function book()
    {
        return $this->belongsTo(Book::class);
    }
}
