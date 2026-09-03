<?php

namespace App\Http\Controllers;

use App\Models\AccessibilityProject;
use App\Models\ProjectShareLink;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;

class ProjectShareLinkController extends Controller
{
    use AuthorizesRequests;

    public function store(Request $request, AccessibilityProject $project)
    {
        $this->authorize('update', $project);

        $validated = $request->validate([
            'expires_at' => 'nullable|date|after:today',
        ]);

        $link = ProjectShareLink::create([
            'project_id' => $project->id,
            'created_by' => auth()->id(),
            'expires_at' => $validated['expires_at'] ?? null,
        ]);

        return back()->with('success', 'Share link created successfully.')
            ->with('share_link_token', $link->token);
    }

    public function destroy(AccessibilityProject $project, ProjectShareLink $link)
    {
        $this->authorize('update', $project);

        $link->delete();

        return back()->with('success', 'Share link deleted.');
    }
}
