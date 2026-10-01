<?php

namespace App\Http\Controllers;

use App\Models\AccessibilityProject;
use App\Models\AccessibilityReport;
use App\Models\ProjectShareLink;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;

class ProjectShareLinkController extends Controller
{
    use AuthorizesRequests;

    public function store(Request $request, AccessibilityProject $project, AccessibilityReport $report)
    {
        $this->authorize('update', $project);

        abort_unless($report->project_id === $project->id, 404);

        $validated = $request->validate([
            'expires_at' => ['required', 'date', 'after:now'],
        ]);

        if (blank($report->html_content)) {
            return back()->withErrors(['report' => __('This report has no saved document to share.')]);
        }

        $link = ProjectShareLink::create([
            'project_id' => $project->id,
            'report_id' => $report->id,
            'created_by' => auth()->id(),
            'expires_at' => $validated['expires_at'],
        ]);

        return back()->with('success', __('Share link created successfully.'))
            ->with('share_link_token', $link->token);
    }

    public function destroy(AccessibilityProject $project, ProjectShareLink $shareLink)
    {
        $this->authorize('update', $project);

        $project->shareLinks()->whereKey($shareLink->id)->firstOrFail()->delete();

        return back()->with('success', __('Share link deleted.'));
    }
}
