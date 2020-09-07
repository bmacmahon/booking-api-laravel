<?php

namespace App\Booking\Model;

use Illuminate\Database\Eloquent\Model;

/**
 * Class Clinic
 * @package App\Booking\Model
 */
class Clinic extends Model
{
    const DEFAULT_ID = 1;

    protected $table = 'clinic';

    protected $fillable = ['name'];
}
