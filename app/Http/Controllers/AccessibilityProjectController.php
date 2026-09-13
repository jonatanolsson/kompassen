<?php

namespace App\Http\Controllers;

use App\Models\AccessibilityProject;
use App\Models\ProjectMember;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class AccessibilityProjectController extends Controller
{
    use AuthorizesRequests;

    public function index(Request $request)
    {
        $search = $request->string('search')->trim()->toString();

        $projects = auth()->user()->team->accessibilityProjects()
            ->with(['pages:id,project_id,url'])
            ->withCount(['pages', 'issues'])
            ->when($search !== '', function (Builder $query) use ($search): void {
                $query->where(function (Builder $query) use ($search): void {
                    $query->where('name', 'like', "%{$search}%")
                        ->orWhere('description', 'like', "%{$search}%")
                        ->orWhereHas('pages', function (Builder $query) use ($search): void {
                            $query->where('url', 'like', "%{$search}%");
                        });
                });
            })
            ->latest('updated_at')
            ->get();

        return view('accessibility.projects.index', compact('projects', 'search'));
    }

    public function create()
    {
        return view('accessibility.projects.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'target_wcag_level' => 'required|in:A,AA,AAA',
            'audit_date' => 'nullable|date',
            'client_logo' => ['nullable', 'image', 'max:2048'],
        ]);

        if ($request->hasFile('client_logo')) {
            $validated['client_logo'] = $request->file('client_logo')->store('client-logos', 'public');
        } else {
            unset($validated['client_logo']);
        }

        $project = auth()->user()->team->accessibilityProjects()->create($validated);

        // Add creator as owner
        ProjectMember::create([
            'project_id' => $project->id,
            'user_id' => auth()->id(),
            'role' => 'owner',
        ]);

        return redirect()->route('accessibility-projects.show', $project)
            ->with('success', 'Project created successfully.');
    }

    public function show(AccessibilityProject $project)
    {
        $this->authorize('view', $project);

        $project->load([
            'pages',
            'issues.page',
            'issues.wcagCriteria',
            'issues.assignedTo',
            'shareLinks',
            'members.user',
            'methodologies',
        ]);

        return view('accessibility.projects.show', compact('project'));
    }

    public function edit(AccessibilityProject $project)
    {
        $this->authorize('update', $project);

        return view('accessibility.projects.edit', compact('project'));
    }

    public function update(Request $request, AccessibilityProject $project)
    {
        $this->authorize('update', $project);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'target_wcag_level' => 'required|in:A,AA,AAA',
            'audit_date' => 'nullable|date',
            'status' => 'required|in:planning,in_progress,completed',
            'client_logo' => ['nullable', 'image', 'max:2048'],
            'remove_client_logo' => ['nullable', 'boolean'],
        ]);

        if ($request->boolean('remove_client_logo')) {
            if ($project->client_logo) {
                Storage::disk('public')->delete($project->client_logo);
            }
            $validated['client_logo'] = null;
        } elseif ($request->hasFile('client_logo')) {
            if ($project->client_logo) {
                Storage::disk('public')->delete($project->client_logo);
            }
            $validated['client_logo'] = $request->file('client_logo')->store('client-logos', 'public');
        } else {
            unset($validated['client_logo']);
        }

        unset($validated['remove_client_logo']);
        $project->update($validated);

        return redirect()->route('accessibility-projects.show', $project)
            ->with('success', 'Project updated successfully.');
    }

    public function destroy(AccessibilityProject $project)
    {
        $this->authorize('delete', $project);

        if ($project->client_logo) {
            Storage::disk('public')->delete($project->client_logo);
        }

        $project->delete();

        return redirect()->route('accessibility-projects.index')
            ->with('success', 'Project deleted successfully.');
    }
}
