<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Settings extends Model
{
    protected $fillable = [
        'key', 'label', 'value', 'is_live'
    ];

    protected $casts = [
        'is_live' => 'boolean',
    ];


    public function scopeLive($query)
    {
        return $query->where('is_live', true);
    }

    public static function byKey(string $key)
    {
        $item = self::query()->where('key', $key)->first();
        if (!$item) {
            return null;
        }
        return $item->value;
    }


}
