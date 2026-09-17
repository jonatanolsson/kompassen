<?php

namespace App\Policies;

use App\Models\User;
use App\Models\WcagSuccessCriterion;

class WcagSuccessCriterionPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->team_id !== null;
    }

    public function view(User $user, WcagSuccessCriterion $criterion): bool
    {
        return $this->viewAny($user);
    }

    public function create(User $user): bool
    {
        return $this->viewAny($user);
    }

    public function update(User $user, WcagSuccessCriterion $criterion): bool
    {
        return $this->viewAny($user);
    }

    public function delete(User $user, WcagSuccessCriterion $criterion): bool
    {
        return $this->viewAny($user);
    }
}
