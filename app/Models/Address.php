<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Address extends Model
{
    protected $fillable = [
        'user_id', 'type', 'label',
        'name', 'company', 'vat_number',
        'line1', 'line2', 'city',
        'state', 'zip', 'country',
        'is_default'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
