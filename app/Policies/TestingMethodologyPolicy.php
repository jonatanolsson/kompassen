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
        return $this->viewAny($user) && ($testingMethodology->team_id === null || (string) $testingMethodology->team_id === (string) $user->team_id);
    }

    public function create(User $user): bool
    {
        return $user->canEditTeamKnowledge();
    }

    public function update(User $user, TestingMethodology $testingMethodology): bool
    {
        return $user->canEditTeamKnowledge() && $testingMethodology->team_id !== null && (string) $testingMethodology->team_id === (string) $user->team_id;
    }

    public function delete(User $user, TestingMethodology $testingMethodology): bool
    {
        return $this->update($user, $testingMethodology);
    }
}
