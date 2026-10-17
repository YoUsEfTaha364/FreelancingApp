<?php

namespace App\Policies;

use App\Models\Job;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class JobPolicy
{
   public function update(User $user, Job $job): bool
    {
        return $job->user_id === $user->id;
    }
   public function show(User $user, Job $job): bool
    {
        return $job->user_id === $user->id;
    }

    public function delete(User $user, Job $job): bool
    {
        return $job->user_id === $user->id;
    }

    public function changeStatus(User $user, Job $job): bool
    {
        return $job->user_id === $user->id;
    }
}
