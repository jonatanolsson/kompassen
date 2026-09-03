<?php

namespace App\Http\Controllers;

use App\Models\AccessibilityProject;
use App\Models\AccessibilityPage;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;

class AccessibilityPageController extends Controller
{
    use AuthorizesRequests;

    public function create(AccessibilityProject $project)
    {
        $this->authorize('update', $project);

        return view('accessibility.pages.create', compact('project'));
    }

    public function store(Request $request, AccessibilityProject $project)
    {
        $this->authorize('update', $project);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'url' => 'nullable|url',
            'description' => 'nullable|string',
        ]);

        $project->pages()->create($validated);

        return redirect()->route('accessibility-projects.show', $project)
            ->with('success', 'Page created successfully.');
    }

    public function edit(AccessibilityProject $project, AccessibilityPage $page)
    {
        $this->authorize('update', $project);

        return view('accessibility.pages.edit', compact('project', 'page'));
    }

    public function update(Request $request, AccessibilityProject $project, AccessibilityPage $page)
    {
        $this->authorize('update', $project);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'url' => 'nullable|url',
            'description' => 'nullable|string',
        ]);

        $page->update($validated);

        return redirect()->route('accessibility-projects.show', $project)
            ->with('success', 'Page updated successfully.');
    }

    public function destroy(AccessibilityProject $project, AccessibilityPage $page)
    {
        $this->authorize('update', $project);

        $page->delete();

        return redirect()->route('accessibility-projects.show', $project)
            ->with('success', 'Page deleted successfully.');
    }
}
