<?php

namespace App\Policies;

use App\Models\User;
use App\Models\WcagCriterionExample;

class WcagCriterionExamplePolicy
{
    public function create(User $user): bool
    {
        return $user->canEditTeamKnowledge();
    }

    public function update(User $user, WcagCriterionExample $example): bool
    {
        return $user->canEditTeamKnowledge() && $example->team_id !== null && (string) $example->team_id === (string) $user->team_id;
    }

    public function delete(User $user, WcagCriterionExample $example): bool
    {
        return $this->update($user, $example);
    }
}
