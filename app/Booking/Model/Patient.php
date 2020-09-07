<?php

namespace App\Booking\Model;

use Illuminate\Database\Eloquent\Model;

/**
 * Class Patient
 * @package App\Booking\Model
 */
class Patient extends Model
{
    protected $table = 'patient';

    protected $fillable = ['first_name', 'last_name'];
}
