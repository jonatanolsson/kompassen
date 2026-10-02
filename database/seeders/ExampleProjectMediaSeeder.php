<?php

namespace Database\Seeders;

use App\Models\AccessibilityIssue;
use App\Models\AccessibilityIssueAttachment;
use App\Models\AccessibilityProject;
use App\Models\User;
use App\Models\WcagCriterionExample;
use App\Models\WcagCriterionExampleAttachment;
use App\Models\WcagSuccessCriterion;
use App\Services\IssueDiagramSvg;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ExampleProjectMediaSeeder extends Seeder
{
    public function run(): void
    {
        $user = User::query()
            ->where('email', 'test@example.com')
            ->firstOrFail();
        $project = AccessibilityProject::query()
            ->where('team_id', $user->team_id)
            ->where('name', 'Nordiska E-tjänster')
            ->firstOrFail();

        $teamId = (string) $user->team_id;
        $altTextExample = $this->upsertExample(
            $this->criterion('1.1.1'),
            $teamId,
            [
                'title' => 'Informativ bild med alternativtext',
                'description' => 'Bildens syfte ska framgå av alternativtexten. Dekorativa bilder ska ha tom alternativtext så att de kan hoppas över.',
                'code_snippet' => '<img src="produktoversikt.png" alt="Produktöversikt för ansökan">',
                'code_language' => 'html',
                'url' => 'https://www.w3.org/WAI/tutorials/images/',
                'url_label' => 'W3C Images Tutorial',
            ],
        );
        $this->upsertExampleAttachment(
            $altTextExample,
            'demo-alt-text.png',
            'demo-alt-text.png',
            'Illustration av en bild med informativ alternativtext.',
            0,
        );

        $contrastSource = <<<'MERMAID'
flowchart LR
    A[Textfärg] -->|2,8:1| B[Vit bakgrund]
    B --> C[Underkänd kontrast]
    C --> D[Minst 4,5:1]
MERMAID;
        $contrastExample = $this->upsertExample(
            $this->criterion('1.4.3'),
            $teamId,
            [
                'title' => 'Kontrastmätning för vanlig text',
                'description' => 'Kontrollera text, länkar och fokusmarkeringar mot alla bakgrunder som används i gränssnittet.',
                'code_snippet' => $contrastSource,
                'code_language' => 'mermaid',
                'mermaid_svg' => $this->contrastDiagramSvg(),
                'url' => 'https://www.w3.org/WAI/WCAG21/Understanding/contrast-minimum.html',
                'url_label' => 'W3C Understanding 1.4.3',
            ],
        );
        $this->upsertExampleAttachment(
            $contrastExample,
            'demo-contrast.png',
            'demo-contrast.png',
            'Syntetisk skärmbild som visar text med otillräcklig kontrast.',
            0,
        );

        $validationSource = <<<'MERMAID'
flowchart TD
    A[Skicka formulär] --> B[Validera fält]
    B -->|Fel| C[aria-invalid=true]
    C --> D[aria-describedby]
    D --> E[Feltext nära fältet]
MERMAID;
        $validationExample = $this->upsertExample(
            $this->criterion('3.3.1'),
            $teamId,
            [
                'title' => 'Formulärfel kopplas till rätt fält',
                'description' => 'Felmeddelandet ska identifiera vilket fält som behöver korrigeras och vara programmässigt kopplat till fältet.',
                'code_snippet' => $validationSource,
                'code_language' => 'mermaid',
                'mermaid_svg' => $this->validationDiagramSvg(),
                'url' => 'https://www.w3.org/WAI/WCAG21/Understanding/error-identification.html',
                'url_label' => 'W3C Understanding 3.3.1',
            ],
        );
        $this->upsertExampleAttachment(
            $validationExample,
            'demo-form-error.png',
            'demo-form-error.png',
            'Syntetisk skärmbild av ett formulärfält med valideringsfel.',
            0,
        );

        $contrastIssue = $this->issue($project, 'Sekundär text och länkar har för låg kontrast');
        $contrastIssue->update([
            'impact' => '<p>Personer med nedsatt kontrastseende kan missa information och länkar i kort och sidfot.</p>',
        ]);
        $contrastIssue->examples()->syncWithoutDetaching([
            $contrastExample->id => ['sort_order' => 0],
        ]);
        $this->upsertIssueAttachment(
            $contrastIssue,
            'demo-contrast.png',
            'kontrastgranskning.png',
        );

        $validationIssue = $this->issue($project, 'Valideringsfel är inte kopplade till formulärfälten');
        $validationIssue->update([
            'impact' => '<p>Skärmläsaranvändare kan missa vilket fält som behöver korrigeras och fastna i formuläret.</p>',
            'mermaid_diagram' => $validationSource,
            'mermaid_svg' => $this->validationDiagramSvg(),
            'mermaid_source_hash' => IssueDiagramSvg::hashSource($validationSource),
        ]);
        $validationIssue->examples()->syncWithoutDetaching([
            $validationExample->id => ['sort_order' => 0],
        ]);
        $this->upsertIssueAttachment(
            $validationIssue,
            'demo-form-error.png',
            'betalningsformular-fel.png',
        );
    }

    private function criterion(string $number): WcagSuccessCriterion
    {
        return WcagSuccessCriterion::query()
            ->where('standard', 'WCAG 2.1')
            ->where('number', $number)
            ->firstOrFail();
    }

    private function issue(AccessibilityProject $project, string $title): AccessibilityIssue
    {
        return $project->issues()->where('title', $title)->firstOrFail();
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function upsertExample(WcagSuccessCriterion $criterion, string $teamId, array $data): WcagCriterionExample
    {
        $example = $criterion->examples()
            ->where('team_id', $teamId)
            ->where('title', $data['title'])
            ->firstOrNew();

        if (! $example->exists) {
            $example->id = Str::ulid();
        }

        $isMermaid = ($data['code_language'] ?? null) === 'mermaid';
        $source = $isMermaid ? (string) ($data['code_snippet'] ?? '') : null;
        $example->fill([
            ...$data,
            'team_id' => $teamId,
            'mermaid_source_hash' => $source !== null ? IssueDiagramSvg::hashSource($source) : null,
            'mermaid_svg' => $isMermaid ? $data['mermaid_svg'] : null,
        ]);
        $example->save();

        if (! empty($data['url'])) {
            $link = $example->referenceLinks()->where('url', $data['url'])->firstOrNew();
            if (! $link->exists) {
                $link->id = Str::ulid();
            }
            $link->fill([
                'url' => $data['url'],
                'label' => $data['url_label'] ?? null,
                'sort_order' => 0,
            ]);
            $link->save();
        }

        return $example->fresh();
    }

    private function upsertIssueAttachment(
        AccessibilityIssue $issue,
        string $fixture,
        string $originalFilename,
    ): void {
        $attachment = AccessibilityIssueAttachment::query()->firstOrNew([
            'accessibility_issue_id' => $issue->id,
            'original_filename' => $originalFilename,
        ]);
        $this->fillAttachment($attachment, $fixture, "accessibility-issues/{$issue->id}");
        $attachment->save();
    }

    private function upsertExampleAttachment(
        WcagCriterionExample $example,
        string $fixture,
        string $originalFilename,
        string $altText,
        int $sortOrder,
    ): void {
        $attachment = WcagCriterionExampleAttachment::query()->firstOrNew([
            'wcag_criterion_example_id' => $example->id,
            'original_filename' => $originalFilename,
        ]);
        $this->fillAttachment($attachment, $fixture, "wcag-examples/{$example->id}", [
            'alt_text' => $altText,
            'sort_order' => $sortOrder,
        ]);
        $attachment->save();
    }

    /**
     * @param  array<string, mixed>  $extra  Attachment-specific attributes.
     */
    private function fillAttachment(
        AccessibilityIssueAttachment|WcagCriterionExampleAttachment $attachment,
        string $fixture,
        string $directory,
        array $extra = [],
    ): void {
        $fixturePath = database_path("seeders/fixtures/{$fixture}");
        $contents = File::get($fixturePath);
        $extension = pathinfo($fixture, PATHINFO_EXTENSION);

        if (! $attachment->exists) {
            $attachment->id = Str::ulid();
            $attachment->filename = Str::ulid().".{$extension}";
            $attachment->path = "{$directory}/{$attachment->filename}";
        }

        if (! Storage::disk('public')->put($attachment->path, $contents)) {
            throw new \RuntimeException("Could not store seeded media at {$attachment->path}.");
        }

        $attachment->fill([
            'filename' => $attachment->filename,
            'original_filename' => $attachment->original_filename,
            'mime_type' => File::mimeType($fixturePath),
            'size' => File::size($fixturePath),
            'path' => $attachment->path,
            ...$extra,
        ]);
    }

    private function contrastDiagramSvg(): string
    {
        return (new IssueDiagramSvg)->sanitize(<<<'SVG'
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 720 280" role="img" aria-roledescription="diagram">
<title>Kontrastflöde</title>
<rect x="20" y="80" width="180" height="90" rx="12" fill="#fee2e2" stroke="#dc2626" stroke-width="3"/>
<rect x="270" y="80" width="180" height="90" rx="12" fill="#e0f2fe" stroke="#0284c7" stroke-width="3"/>
<rect x="520" y="80" width="180" height="90" rx="12" fill="#dcfce7" stroke="#16a34a" stroke-width="3"/>
<path d="M200 125 L270 125" fill="none" stroke="#52525b" stroke-width="3"/>
<path d="M450 125 L520 125" fill="none" stroke="#52525b" stroke-width="3"/>
<text x="110" y="120" text-anchor="middle" font-family="Arial" font-size="20" font-weight="bold">2,8:1</text>
<text x="110" y="148" text-anchor="middle" font-family="Arial" font-size="16">underkänd</text>
<text x="360" y="120" text-anchor="middle" font-family="Arial" font-size="20" font-weight="bold">justera färg</text>
<text x="360" y="148" text-anchor="middle" font-family="Arial" font-size="16">testa igen</text>
<text x="610" y="120" text-anchor="middle" font-family="Arial" font-size="20" font-weight="bold">4,5:1</text>
<text x="610" y="148" text-anchor="middle" font-family="Arial" font-size="16">godkänd</text>
</svg>
SVG, 'seed');
    }

    private function validationDiagramSvg(): string
    {
        return (new IssueDiagramSvg)->sanitize(<<<'SVG'
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 720 330" role="img" aria-roledescription="diagram">
<title>Valideringsflöde</title>
<rect x="250" y="20" width="220" height="60" rx="12" fill="#e0f2fe" stroke="#0284c7" stroke-width="3"/>
<rect x="250" y="130" width="220" height="60" rx="12" fill="#fef3c7" stroke="#d97706" stroke-width="3"/>
<rect x="250" y="240" width="220" height="60" rx="12" fill="#dcfce7" stroke="#16a34a" stroke-width="3"/>
<path d="M360 80 L360 130" fill="none" stroke="#52525b" stroke-width="3"/>
<path d="M360 190 L360 240" fill="none" stroke="#52525b" stroke-width="3"/>
<text x="360" y="56" text-anchor="middle" font-family="Arial" font-size="20" font-weight="bold">Skicka formulär</text>
<text x="360" y="166" text-anchor="middle" font-family="Arial" font-size="20" font-weight="bold">Fel upptäcks</text>
<text x="360" y="276" text-anchor="middle" font-family="Arial" font-size="20" font-weight="bold">Kopplad feltext</text>
</svg>
SVG, 'seed');
    }
}
