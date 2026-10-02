<?php

use App\Models\AccessibilityProject;
use App\Models\User;
use App\Models\WcagCriterionExample;
use App\Services\IssueDiagramSvg;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Support\Facades\Storage;

test('example project includes images and current diagram examples', function () {
    Storage::fake('public');

    $this->seed(DatabaseSeeder::class);

    $user = User::query()->where('email', 'test@example.com')->firstOrFail();
    $project = AccessibilityProject::query()
        ->where('team_id', $user->team_id)
        ->where('name', 'Nordiska E-tjänster')
        ->firstOrFail();
    $contrastExample = WcagCriterionExample::query()
        ->where('title', 'Kontrastmätning för vanlig text')
        ->firstOrFail();
    $validationExample = WcagCriterionExample::query()
        ->where('title', 'Formulärfel kopplas till rätt fält')
        ->firstOrFail();
    $validationIssue = $project->issues()
        ->where('title', 'Valideringsfel är inte kopplade till formulärfälten')
        ->firstOrFail();

    expect($contrastExample->team_id)
        ->toBe((string) $user->team_id)
        ->and(IssueDiagramSvg::isCurrent(
            $contrastExample->code_snippet,
            $contrastExample->mermaid_svg,
            $contrastExample->mermaid_source_hash,
        ))->toBeTrue()
        ->and($validationExample->attachments)->toHaveCount(1)
        ->and($validationIssue->examples->pluck('id')->all())
        ->toContain((string) $validationExample->id)
        ->and($validationIssue->attachments)->toHaveCount(1)
        ->and(IssueDiagramSvg::isCurrent(
            $validationIssue->mermaid_diagram,
            $validationIssue->mermaid_svg,
            $validationIssue->mermaid_source_hash,
        ))->toBeTrue();

    Storage::disk('public')->assertExists($contrastExample->attachments->firstOrFail()->path);
    Storage::disk('public')->assertExists($validationIssue->attachments->firstOrFail()->path);
});
