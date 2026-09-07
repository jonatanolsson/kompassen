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
        $markdown .= "**Difficulty**: {$data['difficulty']}\n";
        $markdown .= "**Status**: {$data['status']}\n\n";

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
                $markdown .= "- **{$criterion['number']}** - {$criterion['name']}\n";
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

        $csv = "Title,Severity,Difficulty,Status,Description,Component Area,Page,WCAG Criteria,Attachments\n";
        $csv .= $this->escapeCsvField($data['title']).',';
        $csv .= $this->escapeCsvField($data['severity']).',';
        $csv .= $this->escapeCsvField($data['difficulty']).',';
        $csv .= $this->escapeCsvField($data['status']).',';
        $csv .= $this->escapeCsvField($data['description']).',';
        $csv .= $this->escapeCsvField($data['component_area']).',';
        $csv .= $this->escapeCsvField($data['page_name']).',';
        $csv .= $this->escapeCsvField(implode('; ', array_column($data['wcag_criteria'], 'number'))).',';
        $csv .= $this->escapeCsvField(implode('; ', $data['attachments']))."\n";

        return $csv;
    }

    private function getExportData(): array
    {
        $wcagCriteria = $this->issue->wcagCriteria()
            ->get()
            ->map(fn ($criterion) => [
                'number' => $criterion->number,
                'name' => $criterion->name_sv ?? $criterion->name_en,
                'level' => $criterion->level,
            ])
            ->toArray();

        $attachments = $this->issue->attachments()
            ->get()
            ->map(fn ($attachment) => $attachment->original_filename)
            ->toArray();

        return [
            'id' => $this->issue->id,
            'title' => $this->issue->title,
            'description' => $this->issue->description,
            'severity' => Str::title($this->issue->severity),
            'difficulty' => Str::title($this->issue->difficulty),
            'status' => Str::title($this->issue->status),
            'component_area' => $this->issue->component_area,
            'page_name' => $this->issue->page?->name,
            'wcag_criteria' => $wcagCriteria,
            'attachments' => $attachments,
            'created_at' => $this->issue->created_at->toIso8601String(),
            'updated_at' => $this->issue->updated_at->toIso8601String(),
        ];
    }

    private function escapeCsvField(string $field): string
    {
        if (empty($field)) {
            return '';
        }

        if (str_contains($field, ',') || str_contains($field, '"') || str_contains($field, "\n")) {
            return '"'.str_replace('"', '""', $field).'"';
        }

        return $field;
    }
}
