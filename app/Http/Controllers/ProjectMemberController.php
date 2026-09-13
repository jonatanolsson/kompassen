<?php

namespace App\Http\Controllers;

use App\Models\AccessibilityProject;
use App\Models\ProjectMember;
use App\Models\User;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;

class ProjectMemberController extends Controller
{
    use AuthorizesRequests;

    public function store(Request $request, AccessibilityProject $project)
    {
        $this->authorize('manageMembers', $project);

        $validated = $request->validate([
            'email' => 'required|email|exists:users,email',
            'role' => 'required|in:owner,editor,viewer',
        ]);

        $user = User::where('email', $validated['email'])->firstOrFail();

        // Check if user is already a member
        if ($project->members()->where('user_id', $user->id)->exists()) {
            return back()->withErrors(['email' => __('User is already a member of this project.')]);
        }

        // Check if user is in the same team
        if ((string) $user->team_id !== (string) $project->team_id) {
            return back()->withErrors(['email' => __('User must be in the same team.')]);
        }

        ProjectMember::create([
            'project_id' => $project->id,
            'user_id' => $user->id,
            'role' => $validated['role'],
        ]);

        return back()->with('success', __('Member added successfully.'));
    }

    public function update(Request $request, AccessibilityProject $project, ProjectMember $member)
    {
        $this->authorize('manageMembers', $project);

        $validated = $request->validate([
            'role' => 'required|in:owner,editor,viewer',
        ]);

        $member->update($validated);

        return back()->with('success', __('Member role updated.'));
    }

    public function destroy(AccessibilityProject $project, ProjectMember $member)
    {
        $this->authorize('manageMembers', $project);

        $member->delete();

        return back()->with('success', __('Member removed.'));
    }
}
