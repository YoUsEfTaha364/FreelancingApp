<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Negotiation extends Model
{
    protected $fillable = [
        'proposal_id',
        'proposed_by',
        'proposed_amount',
        'message',
        'status',
        'responded_at',
    ];

    protected function casts(): array
    {
        return [
            'proposed_amount' => 'decimal:2',
            'responded_at' => 'datetime',
        ];
    }

    public function proposal(): BelongsTo
    {
        return $this->belongsTo(Proposal::class);
    }

    public function proposer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'proposed_by');
    }
}
