<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Vendor extends Model
{
    protected $fillable = [
        'user_id', 'type', 'stripe_account_id',
        'vat_number', 'iban', 'commission_rate',
        'status'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
