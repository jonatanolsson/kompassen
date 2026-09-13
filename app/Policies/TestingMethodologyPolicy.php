<?php

namespace App\Policies;

use App\Models\TestingMethodology;
use App\Models\User;

class TestingMethodologyPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->team_id !== null;
    }

    public function view(User $user, TestingMethodology $testingMethodology): bool
    {
        return $this->viewAny($user);
    }

    public function create(User $user): bool
    {
        return $this->viewAny($user);
    }

    public function update(User $user, TestingMethodology $testingMethodology): bool
    {
        return $this->viewAny($user);
    }

    public function delete(User $user, TestingMethodology $testingMethodology): bool
    {
        return $this->viewAny($user);
    }
}
