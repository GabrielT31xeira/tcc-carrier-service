<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TravelProposal extends Model
{
    use HasFactory;

    protected $table = 'travel_proposal';
    public $timestamps = false;
    protected $fillable = [
        'travel_id',
        'proposal_id',
    ];

    public function carrier(): BelongsTo
    {
        return $this->belongsTo(Travel::class, 'travel_id', 'id_travel');
    }

    public function proposal(): BelongsTo
    {
        return $this->belongsTo(Proposal::class, 'proposal_id', 'id_proposal');
    }
}
