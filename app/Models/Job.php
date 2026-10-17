<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Job extends Model
{
    /**
     * Named 'job_posts' to avoid collision with Laravel's built-in queue 'jobs' table.
     */
    protected $table = 'job_posts';

    protected $fillable = [
        'user_id',
        'category_id',
        'title',
        'description',
        'budget_min',
        'budget_max',
        'experience_level',
        'deadline',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'deadline'   => 'date',
            'budget_min' => 'decimal:2',
            'budget_max' => 'decimal:2',
        ];
    }

    /**
     * The client (User) who owns this job.
     */
    public function client(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function skills(): BelongsToMany
    {
        return $this->belongsToMany(Skill::class, 'job_skill');
    }
}
