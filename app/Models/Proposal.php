<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Proposal extends Model
{
    protected $fillable = [
        'freelancer_id',
        'job_id',
        'cover_letter',
        'estimated_duration',
        'bid_amount',
        'agreed_amount',
    ];

    protected function casts(): array
    {
        return [
            'bid_amount' => 'decimal:2',
            'agreed_amount' => 'decimal:2',
            'estimated_duration' => 'integer',
        ];
    }

    public function freelancer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'freelancer_id');
    }

    public function job(): BelongsTo
    {
        return $this->belongsTo(Job::class, 'job_id');
    }

    public function negotiations(): HasMany
    {
        return $this->hasMany(Negotiation::class);
    }
}
