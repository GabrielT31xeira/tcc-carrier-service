<?php

namespace App\Models;

use App\traits\UUID;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Proposal extends Model
{
    use HasFactory, UUID;

    protected $primaryKey = 'id_proposal';
    protected $table = 'proposal';
    protected $fillable = [
        'client_travel_id',
        'accepted',
        'date_arrival',
        'date_output',
        'price'
    ];

    public function travel(): BelongsToMany
    {
        return $this->belongsToMany(Travel::class, 'travel_proposal', 'proposal_id', 'travel_id');
    }
}
