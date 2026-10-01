<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class TeamKnowledgeEditorController extends Controller
{
    public function index(Request $request): View
    {
        abort_unless($request->user()->isTeamOwner(), 403);

        $users = $request->user()->team->users()->orderBy('name')->get();

        return view('teams.knowledge-editors', compact('users'));
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        abort_unless($request->user()->isTeamOwner(), 403);
        abort_unless((string) $user->team_id === (string) $request->user()->team_id, 404);
        abort_if($user->is($request->user()), 403);

        $validated = $request->validate([
            'is_knowledge_editor' => ['required', Rule::in(['0', '1'])],
        ]);
        $user->update(['is_knowledge_editor' => (bool) $validated['is_knowledge_editor']]);

        return back()->with('success', __('Knowledge editor role updated.'));
    }
}
