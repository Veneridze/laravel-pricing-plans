<?php

namespace Veneridze\PricingPlans\Tests\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Veneridze\PricingPlans\Contracts\Subscriber;
use Veneridze\PricingPlans\Traits\Subscribable;

class User extends Model implements Subscriber
{
    use Subscribable;
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];
}
