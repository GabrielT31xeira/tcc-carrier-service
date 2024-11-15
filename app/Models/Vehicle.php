<?php

namespace App\Models;

use App\traits\UUID;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Vehicle extends Model
{
    use HasFactory, UUID;

    protected $primaryKey = 'id_vehicle';

    protected $table = 'vehicles';
    protected $fillable = [
        'plate',
        'vehicle_type',
        'brand',
        'model',
        'model_year',
        'year_manufacture'
    ];
}
