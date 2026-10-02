<?php

namespace App\Http\Controllers;

use App\Models\AccessibilityProject;
use App\Models\TestingMethodology;
use Illuminate\Contracts\View\View;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class ProjectMethodologyController extends Controller
{
    use AuthorizesRequests;

    public function show(AccessibilityProject $project, TestingMethodology $methodology): View
    {
        $this->authorize('view', $project);

        $assignedMethodology = $project->methodologies()
            ->availableToTeam((string) $project->team_id)
            ->whereKey($methodology->getKey())
            ->firstOrFail();

        return view('accessibility.projects.methodologies.show', [
            'project' => $project,
            'methodology' => $assignedMethodology,
        ]);
    }
}
