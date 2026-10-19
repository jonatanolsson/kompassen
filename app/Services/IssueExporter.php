<?php

namespace App\Services;

use App\Models\AccessibilityIssue;
use Illuminate\Support\Str;

class IssueExporter
{
    public function __construct(
        private AccessibilityIssue $issue,
    ) {}

    public function export(string $format = 'json'): string
    {
        return match ($format) {
            'json' => $this->toJson(),
            'markdown' => $this->toMarkdown(),
            'csv' => $this->toCsv(),
            default => $this->toJson(),
        };
    }

    private function toJson(): string
    {
        $data = $this->getExportData();

        return json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
    }

    private function toMarkdown(): string
    {
        $data = $this->getExportData();

        $markdown = "# {$data['title']}\n\n";
        $markdown .= "**Severity**: {$data['severity']}\n";
        $markdown .= '**'.__('Estimated remediation effort')."**: {$data['difficulty']}\n";
        $markdown .= "**Status**: {$data['status']}\n\n";
        $markdown .= '**'.__('Project WCAG target')."**: WCAG {$data['project_target_wcag_version']}\n";

        if ($data['summary']) {
            $markdown .= "## Summary\n\n{$data['summary']}\n\n";
        }

        if ($data['description']) {
            $markdown .= "## Description\n\n{$data['description']}\n\n";
        }

        if ($data['component_area']) {
            $markdown .= "## Component Area\n\n{$data['component_area']}\n\n";
        }

        if ($data['page_name']) {
            $markdown .= "**Page**: {$data['page_name']}\n";
        }

        if (! empty($data['wcag_criteria'])) {
            $markdown .= "\n## WCAG Success Criteria\n\n";
            foreach ($data['wcag_criteria'] as $criterion) {
                $markdown .= "- **{$criterion['label']}** - {$criterion['name']}\n";
            }
        }

        if (! empty($data['attachments'])) {
            $markdown .= "\n## Attachments\n\n";
            foreach ($data['attachments'] as $attachment) {
                $markdown .= "- {$attachment}\n";
            }
        }

        return $markdown;
    }

    private function toCsv(): string
    {
        $data = $this->getExportData();

        $csv = 'Title,Summary,Severity,'.$this->escapeCsvField(__('Estimated remediation effort')).',Status,Description,Component Area,Page,'.__('Project WCAG target').",WCAG Criteria,Attachments\n";
        $csv .= $this->escapeCsvField($data['title']).',';
        $csv .= $this->escapeCsvField($data['summary']).',';
        $csv .= $this->escapeCsvField($data['severity']).',';
        $csv .= $this->escapeCsvField($data['difficulty']).',';
        $csv .= $this->escapeCsvField($data['status']).',';
        $csv .= $this->escapeCsvField($data['description']).',';
        $csv .= $this->escapeCsvField($data['component_area']).',';
        $csv .= $this->escapeCsvField($data['page_name']).',';
        $csv .= $this->escapeCsvField($data['project_target_wcag_version']).',';
        $csv .= $this->escapeCsvField(implode('; ', array_column($data['wcag_criteria'], 'label'))).',';
        $csv .= $this->escapeCsvField(implode('; ', $data['attachments']))."\n";

        return $csv;
    }

    private function getExportData(): array
    {
        $this->issue->loadMissing(['project', 'page']);
        $targetVersion = $this->issue->project->target_wcag_version;
        $wcagCriteria = $this->issue->wcagCriteria()
            ->get()
            ->map(fn ($criterion) => [
                'standard' => $criterion->standard,
                'number' => $criterion->number,
                'name' => $criterion->name_sv ?? $criterion->name_en,
                'level' => $criterion->level,
                'nice_to_have' => $criterion->isOutsideProjectTargetVersion($targetVersion),
                'label' => $criterion->standard.' '.$criterion->number.($criterion->isOutsideProjectTargetVersion($targetVersion) ? ' ('.__('Nice to have').')' : ''),
            ])
            ->toArray();

        $attachments = $this->issue->attachments()
            ->get()
            ->map(fn ($attachment) => $attachment->original_filename)
            ->toArray();

        return [
            'id' => $this->issue->id,
            'title' => $this->issue->title,
            'summary' => $this->issue->summary,
            'description' => $this->issue->description,
            'severity' => Str::title($this->issue->severity),
            'difficulty' => Str::title($this->issue->difficulty),
            'status' => Str::title($this->issue->status),
            'component_area' => $this->issue->component_area,
            'page_name' => $this->issue->page?->name,
            'project_target_wcag_version' => $targetVersion,
            'wcag_criteria' => $wcagCriteria,
            'attachments' => $attachments,
            'created_at' => $this->issue->created_at->toIso8601String(),
            'updated_at' => $this->issue->updated_at->toIso8601String(),
        ];
    }

    private function escapeCsvField(?string $field): string
    {
        if ($field === null || $field === '') {
            return '';
        }

        if (str_contains($field, ',') || str_contains($field, '"') || str_contains($field, "\n")) {
            return '"'.str_replace('"', '""', $field).'"';
        }

        return $field;
    }
}
