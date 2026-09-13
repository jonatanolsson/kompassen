<?php

namespace App\Policies;

use App\Models\AccessibilityProject;
use App\Models\User;

class AccessibilityProjectPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, AccessibilityProject $project): bool
    {
        // User must be a member of the project
        return $project->members()->where('user_id', $user->id)->exists();
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function update(User $user, AccessibilityProject $project): bool
    {
        // Only owner or editor members can update
        $member = $project->members()->where('user_id', $user->id)->first();

        return $member && in_array($member->role, ['owner', 'editor']);
    }

    public function manageMembers(User $user, AccessibilityProject $project): bool
    {
        $member = $project->members()->where('user_id', $user->id)->first();

        return $member && $member->role === 'owner';
    }

    public function delete(User $user, AccessibilityProject $project): bool
    {
        // Only owner members can delete
        $member = $project->members()->where('user_id', $user->id)->first();

        return $member && $member->role === 'owner';
    }
}
