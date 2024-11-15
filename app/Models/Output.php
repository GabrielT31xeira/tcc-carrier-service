<?php

namespace App\Models;

use App\traits\UUID;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Output extends Model
{
    use HasFactory, UUID;

    protected $primaryKey = 'id_output';

    protected $table = 'output';
    protected $fillable = [
        'city',
        'state',
        'address',
        'latitude',
        'longitude'
    ];
}
