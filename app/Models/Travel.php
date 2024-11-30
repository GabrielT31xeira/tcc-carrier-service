<?php

namespace App\Models;

use App\traits\UUID;
use GuzzleHttp\Client;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Travel extends Model
{
    use HasFactory, UUID;

    protected $table = 'travel';
    protected $primaryKey = 'id_travel';
    protected $fillable = [
        'user_id',
        'arrival_id',
        'output_id',
        'vehicle_id',
    ];

    public function vehicle(): hasOne
    {
        return $this->hasOne(Vehicle::class, 'id_vehicle', 'vehicle_id');
    }

    public function arrival(): hasOne
    {
        return $this->hasOne(Arrival::class, 'id_arrival', 'arrival_id');
    }

    public function output(): hasOne
    {
        return $this->hasOne(Output::class, 'id_output', 'output_id');
    }

}
