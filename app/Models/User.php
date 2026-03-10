<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasFactory, Notifiable, HasRoles;

    protected $fillable = [
        'firstname',
        'lastname',
        'phone_number',
        'email',
        'password',
        'active',
        'stripe_customer_id',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'active' => 'boolean'
    ];

    protected $appends = [
        'groups'
    ];

    public function getGroupsAttribute()
    {
        return $this->roles()->pluck('name')->toArray();
    }

    public function addresses()
    {
        return $this->hasMany(Address::class);
    }

    public function billingAddress()
    {
        return $this->hasOne(Address::class)
            ->where('type', 'billing');
    }

    public function shippingAddress()
    {
        return $this->hasOne(Address::class)
            ->where('type', 'shipping');
    }

    public function vendor()
    {
        return $this->hasOne(Vendor::class);
    }
}
