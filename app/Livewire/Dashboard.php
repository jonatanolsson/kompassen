<?php

namespace App\Livewire;

use App\Models\AccessibilityProject;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\View\View;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.app')]
class Dashboard extends Component
{
    public function render(): View
    {
        $projects = AccessibilityProject::query()
            ->whereHas('members', function (Builder $query): void {
                $query->where('user_id', auth()->id());
            })
            ->withCount([
                'pages',
                'issues',
                'reports',
                'issues as open_issues_count' => function (Builder $query): void {
                    $query->where('status', 'open');
                },
            ])
            ->latest('updated_at')
            ->get();

        return view('livewire.dashboard', [
            'projects' => $projects->take(6),
            'summary' => [
                'projects' => $projects->count(),
                'issues' => $projects->sum('issues_count'),
                'open_issues' => $projects->sum('open_issues_count'),
                'reports' => $projects->sum('reports_count'),
            ],
        ]);
    }
}
