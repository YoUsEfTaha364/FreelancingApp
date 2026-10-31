<?php

namespace App\Policies;

use App\Models\Proposal;
use App\Models\User;

class ProposalPolicy
{
    /**
     * Create a new policy instance.
     */
    public function __construct()
    {
        //
    }


    public function show(User $user, Proposal $proposal): bool
    {
        return $proposal->freelancer_id === $user->id;
    }
    public function withdraw(User $user, Proposal $proposal): bool
    {
        return $proposal->freelancer_id === $user->id;
    }
    public function update(User $user, Proposal $proposal): bool
    {
        return $proposal->freelancer_id === $user->id;
    }
}
