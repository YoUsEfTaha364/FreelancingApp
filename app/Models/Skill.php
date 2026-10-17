<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Skill extends Model
{
    protected $fillable = [
        'name',
    ];

    public function freelancerProfiles(): BelongsToMany
    {
        return $this->belongsToMany(
            FreelancerProfile::class,
            'freelancer_profile_skill'
        );
    }

    public function jobs(): BelongsToMany
    {
        return $this->belongsToMany(Job::class, 'job_skill');
    }
}