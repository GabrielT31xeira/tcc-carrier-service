<?php

namespace App\Models;

use App\traits\UUID;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Arrival extends Model
{
    use HasFactory, UUID;

    protected $primaryKey = 'id_arrival';
    protected $table = 'arrival';
    protected $fillable = [
        'city',
        'state',
        'address',
        'latitude',
        'longitude'
    ];
}
