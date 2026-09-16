<?php

namespace App\Http\Controllers;

use App\Models\AccessibilityPage;
use App\Models\AccessibilityProject;
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
            'resource_type' => 'nullable|in:page,service',
            'url' => 'nullable|url',
            'access_context' => 'nullable|in:not_applicable,public,authenticated,mixed',
            'description' => 'nullable|string',
            'scope' => 'required|in:in_scope,out_of_scope',
        ]);

        $project->pages()->create([
            ...$validated,
            'resource_type' => $validated['resource_type'] ?? 'page',
            'access_context' => $validated['access_context'] ?? 'not_applicable',
        ]);

        return redirect()->route('accessibility-projects.show', $project)
            ->with('success', __('Page or service created successfully.'));
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
            'resource_type' => 'nullable|in:page,service',
            'url' => 'nullable|url',
            'access_context' => 'nullable|in:not_applicable,public,authenticated,mixed',
            'description' => 'nullable|string',
            'scope' => 'required|in:in_scope,out_of_scope',
        ]);

        $page->update([
            ...$validated,
            'resource_type' => $validated['resource_type'] ?? 'page',
            'access_context' => $validated['access_context'] ?? 'not_applicable',
        ]);

        return redirect()->route('accessibility-projects.show', $project)
            ->with('success', __('Page or service updated successfully.'));
    }

    public function destroy(AccessibilityProject $project, AccessibilityPage $page)
    {
        $this->authorize('update', $project);

        $page->delete();

        return redirect()->route('accessibility-projects.show', $project)
            ->with('success', __('Page deleted successfully.'));
    }
}
