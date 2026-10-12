<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ClientProfile extends Model
{
    protected $fillable = [
        'user_id',
        'account_type',
        'company_name',
        'phone',
        'country',
        'city',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}