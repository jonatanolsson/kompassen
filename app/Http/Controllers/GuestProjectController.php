<?php

namespace App\Http\Controllers;

use App\Models\AccessibilityProject;
use App\Models\ProjectShareLink;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class GuestProjectController extends Controller
{
    use AuthorizesRequests;

    public function show(string $token)
    {
        $link = ProjectShareLink::where('token', $token)
            ->with('project.pages.issues')
            ->firstOrFail();

        if ($link->isExpired()) {
            abort(410, 'This share link has expired.');
        }

        $project = $link->project;

        return view('shared-project.show', compact('project', 'link'));
    }

    public function preview(AccessibilityProject $project)
    {
        $this->authorize('view', $project);

        $project->load('pages.issues');
        $link = null;

        return view('shared-project.show', compact('project', 'link'));
    }
}
