<?php

namespace App\Livewire;

use App\Models\AccessibilityIssue;
use App\Models\AccessibilityProject;
use App\Services\IssueExporter;
use Livewire\Component;

class ExportIssueModal extends Component
{
    public AccessibilityProject $project;

    public AccessibilityIssue $issue;

    public string $selectedFormat = 'json';

    public string $exportContent = '';

    public bool $showPreview = false;

    public function mount(AccessibilityProject $project, AccessibilityIssue $issue): void
    {
        $this->project = $project;
        $this->issue = $issue;
        $this->generatePreview();
    }

    public function updatedSelectedFormat(): void
    {
        $this->generatePreview();
    }

    public function generatePreview(): void
    {
        $exporter = new IssueExporter($this->issue);
        $this->exportContent = $exporter->export($this->selectedFormat);
    }

    public function copyToClipboard(): void
    {
        $this->dispatch('copy-to-clipboard', content: $this->exportContent);
        $this->dispatch('notify', message: __('Copied to clipboard!'));
    }

    public function download()
    {
        return redirect()->route('accessibility-issues.export', [
            'project' => $this->project,
            'issue' => $this->issue,
            'format' => $this->selectedFormat,
        ]);
    }

    public function render()
    {
        return view('livewire.export-issue-modal');
    }
}
