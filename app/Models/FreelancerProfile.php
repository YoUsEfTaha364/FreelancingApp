<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FreelancerProfile extends Model
{
    protected $fillable = [
        'user_id',
        'gender',
        'title',
        'biography',
        'phone',
        'country',
        'city',
        'hourly_rate',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function experiences()
    {
        return $this->hasMany(Experience::class);
    }

    public function skills()
    {
        return $this->belongsToMany(Skill::class, "freelancer_profile_skill");
    }

    public function languages()
    {
        return $this->belongsToMany(
            Language::class,
            'freelancer_profile_language'
        )->withPivot('proficiency');
    }
}
