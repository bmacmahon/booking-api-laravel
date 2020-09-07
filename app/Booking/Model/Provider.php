<?php

namespace App\Booking\Model;

use Illuminate\Database\Eloquent\Model;

/**
 * Class Provider
 * @package App\Booking\Model
 */
class Provider extends Model
{
    protected $table = 'provider';

    protected $fillable = ['first_name', 'last_name'];
}
