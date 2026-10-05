<?php

use App\Models\AccessibilityIssue;
use App\Models\AccessibilityPage;
use App\Models\AccessibilityProject;
use App\Models\Team;
use App\Models\WcagSuccessCriterion;
use Carbon\Carbon;

function reportDocumentFixture(): array
{
    $project = new AccessibilityProject;
    $project->setAttribute('id', 'project-1');
    $project->setAttribute('name', 'Example project');
    $project->setAttribute('target_wcag_level', 'AA');
    $project->setAttribute('target_wcag_version', '2.1');
    $project->setAttribute('description', null);
    $project->setAttribute('client_logo', null);
    $team = new Team;
    $team->setAttribute('name', 'Example team');
    $project->setRelation('team', $team);
    $project->setRelation('methodologies', collect());
    $project->setRelation('pages', collect());

    return [
        'title' => 'Accessibility report',
        'project' => $project,
        'scope' => 'Public user journeys',
        'issues' => collect(),
        'counts' => ['critical' => 0, 'major' => 0, 'moderate' => 0, 'minor' => 0],
        'templateVersion' => 10,
        'pageGroups' => collect(),
        'projectWideIssues' => collect(),
        'cleanPages' => collect(),
        'reportImages' => [],
        'reportOptions' => array_fill_keys([
            'evidence',
            'images',
            'code',
            'diagrams',
            'examples',
            'resolution',
            'scope',
            'methodologies',
            'clean_pages',
            'wcag_index',
        ], false),
        'documentMeta' => [
            'version' => 3,
            'generatedAt' => Carbon::parse('2026-09-30 10:00:00'),
            'auditDate' => Carbon::parse('2026-09-29'),
            'creator' => 'Report author',
            'logo' => null,
        ],
    ];
}

function reportDocumentIssue(string $id, string $title, string $severity, string $status, ?AccessibilityPage $page = null): AccessibilityIssue
{
    $issue = new AccessibilityIssue;
    $issue->setAttribute('id', $id);
    $issue->setAttribute('title', $title);
    $issue->setAttribute('severity', $severity);
    $issue->setAttribute('status', $status);
    $issue->setAttribute('page_id', $page?->getKey());
    $issue->setAttribute('description', null);
    $issue->setAttribute('solution_suggestions', null);
    $issue->setAttribute('component_area', null);
    $issue->setAttribute('sample_scope', null);
    $issue->setAttribute('resolution_notes', null);
    $issue->setRelation('page', $page);
    $issue->setRelation('wcagCriteria', collect());
    $issue->setRelation('attachments', collect());
    $issue->setRelation('examples', collect());

    return $issue;
}

function renderReportDocument(array $overrides = []): string
{
    return view('reports.accessibility-report', array_replace(reportDocumentFixture(), $overrides))->render();
}

test('renders compact report navigation and groups issues by page status and severity', function () {
    $page = new AccessibilityPage;
    $page->setAttribute('id', 'page-1');
    $page->setAttribute('name', 'Account page');
    $page->setAttribute('url', 'https://example.com/account');
    $page->setAttribute('resource_type', 'page');
    $page->setAttribute('scope', 'in_scope');
    $page->setAttribute('access_context', 'authenticated');

    $projectIssue = reportDocumentIssue('issue-project', 'Global navigation issue', 'major', 'open');
    $openIssue = reportDocumentIssue('issue-open', 'Missing page label', 'critical', 'open', $page);
    $resolvedIssue = reportDocumentIssue('issue-resolved', 'Resolved heading order', 'major', 'resolved', $page);
    $wontFixIssue = reportDocumentIssue('issue-wont-fix', 'Legacy color contrast', 'minor', 'wont_fix', $page);
    $pageIssues = collect([$openIssue, $resolvedIssue, $wontFixIssue]);
    $project = reportDocumentFixture()['project'];
    $project->setRelation('pages', collect([$page]));

    $html = renderReportDocument([
        'project' => $project,
        'issues' => collect([$projectIssue, ...$pageIssues]),
        'counts' => ['critical' => 1, 'major' => 2, 'moderate' => 0, 'minor' => 1],
        'pageGroups' => collect([[
            'page' => $page,
            'issues' => $pageIssues,
            'statusGroups' => $pageIssues->groupBy('status'),
            'highestSeverity' => 'critical',
        ]]),
        'projectWideIssues' => collect([$projectIssue]),
        'documentMeta' => [
            ...reportDocumentFixture()['documentMeta'],
            'version' => 2,
        ],
    ]);

    expect($html)
        ->toContain('data-report-template="10"')
        ->toContain('href="#project-wide-issues"')
        ->toContain('href="#page-page-1"')
        ->toContain('id="issue-issue-open"')
        ->toContain('id="issue-issue-resolved"')
        ->toContain('id="issue-issue-wont-fix"')
        ->toContain('Global navigation issue')
        ->toContain('Account page')
        ->toContain('1 / 2')
        ->toContain(__('Open report issues'))
        ->toContain(__('Resolved report issues'))
        ->toContain(__('Unfixed report issues'))
        ->toContain(__('Executive Summary'))
        ->toContain(__('Major report severity'))
        ->not->toContain('Stora')
        ->toContain(__('Pages with issues'))
        ->toContain(__('Prioritize open critical and major issues before lower-severity findings.'))
        ->toContain('Public user journeys')
        ->toContain('Report author')
        ->toContain(__('Version :version', ['version' => 2]))
        ->toContain('font-family: Verdana, "DejaVu Sans", sans-serif')
        ->toContain('line-height: 1.7')
        ->toContain('margin-top: 38px')
        ->not->toContain('Sammanfattning för beslutsfattare')
        ->not->toContain('Executive Summary')
        ->toContain('2026')
        ->not->toContain('.dark .report-document')
        ->not->toContain('display: grid');

    expect(strpos($html, 'Global navigation issue'))
        ->toBeLessThan(strpos($html, 'id="pages-with-issues"'));
    expect(strpos($html, 'Missing page label'))
        ->toBeLessThan(strpos($html, 'Resolved heading order'))
        ->toBeLessThan(strpos($html, 'Legacy color contrast'));
});

test('renders optional evidence only when corresponding report options are enabled', function () {
    $issue = reportDocumentIssue('issue-evidence', 'Keyboard trap', 'critical', 'resolved');
    $issue->setAttribute('description', '<p>Focus cannot leave the dialog.</p>');
    $issue->setAttribute('impact', 'Keyboard users cannot complete the task.');
    $issue->setAttribute('solution_suggestions', '<p>Return focus after the dialog closes.</p>');
    $issue->setAttribute('sample_scope', 'all');
    $issue->setAttribute('resolution_notes', 'Fixed in the latest release.');
    $issue->setAttribute('mermaid_diagram', 'flowchart LR; A[Open] --> B[Trap]');
    $diagramSvg = '<svg xmlns="http://www.w3.org/2000/svg" width="100" height="50" viewBox="0 0 100 50"><rect width="100" height="50" fill="#ffffff"/><text x="5" y="20">Diagram</text></svg>';
    $issue->setAttribute('mermaid_svg', $diagramSvg);
    $issue->setRelation('attachments', collect([(object) [
        'path' => 'accessibility-issues/keyboard-trap.png',
        'original_filename' => 'keyboard-trap.png',
        'mime_type' => 'image/png',
    ]]));

    $criterion = new WcagSuccessCriterion;
    $criterion->setAttribute('number', '2.1.2');
    $criterion->setAttribute('name_en', 'No Keyboard Trap');
    $criterion->setAttribute('name_sv', 'Ingen tangentbordsfälla');
    $criterion->setAttribute('level', 'A');
    $criterion->setRelation('pivot', (object) [
        'failure_type' => 'keyboard_trap',
        'comment' => 'Focus remains inside the dialog.',
        'code_snippet' => 'dialog.addEventListener("keydown", trapFocus);',
        'screenshot_path' => 'wcag/keyboard-trap.png',
    ]);
    $issue->setRelation('wcagCriteria', collect([$criterion]));
    $issue->setRelation('examples', collect([(object) [
        'title' => 'Return focus after close',
        'description' => '**Restore focus** to the trigger.',
        'code_snippet' => 'trigger.focus();',
        'code_language' => 'javascript',
        'mermaid_svg' => null,
        'url' => 'https://example.com/guidance',
        'url_label' => 'Implementation guidance',
        'referenceLinks' => collect(),
        'attachments' => collect(),
    ]]));

    $options = array_fill_keys([
        'evidence',
        'images',
        'code',
        'diagrams',
        'examples',
        'resolution',
        'scope',
        'methodologies',
        'clean_pages',
        'wcag_index',
    ], true);
    $data = [
        'issues' => collect([$issue]),
        'projectWideIssues' => collect([$issue]),
        'reportOptions' => $options,
        'reportImages' => [
            'accessibility-issues/keyboard-trap.png' => 'data:image/png;base64,'.base64_encode('issue-image-content'),
            'wcag/keyboard-trap.png' => 'data:image/png;base64,'.base64_encode('criterion-image-content'),
        ],
    ];
    $enabledHtml = renderReportDocument($data);

    expect($enabledHtml)
        ->toContain('id="evidence-appendix"')
        ->toContain('Focus remains inside the dialog.')
        ->toContain('src="data:image/png;base64,'.base64_encode('issue-image-content').'"')
        ->toContain('src="data:image/png;base64,'.base64_encode('criterion-image-content').'"')
        ->toContain('keyboard-trap.png')
        ->toContain('dialog.addEventListener(&quot;keydown&quot;, trapFocus);')
        ->toContain('src="data:image/svg+xml;base64,'.base64_encode($diagramSvg).'"')
        ->not->toContain('flowchart LR; A[Open] --&gt; B[Trap]')
        ->toContain('Return focus after close')
        ->toContain('Fixed in the latest release.')
        ->toContain(__('Entire page or service'))
        ->not->toContain('>all<')
        ->toContain('id="wcag-wcag-21-212"')
        ->toContain('trigger.focus();');

    $disabledHtml = renderReportDocument([
        ...$data,
        'reportOptions' => array_fill_keys(array_keys($options), false),
    ]);

    expect($disabledHtml)
        ->not->toContain('id="evidence-appendix"')
        ->not->toContain('Focus remains inside the dialog.')
        ->not->toContain('keyboard-trap.png')
        ->not->toContain('data:image/svg+xml;base64,')
        ->not->toContain('trigger.focus();')
        ->not->toContain('Fixed in the latest release.')
        ->not->toContain(__('Entire page or service'));
});

test('sanitizes rich text rendered in issue cards', function () {
    $issue = reportDocumentIssue('issue-markdown', 'Unsafe description', 'moderate', 'open');
    $issue->setAttribute('description', '<p>Safe summary</p><script>alert("unsafe")</script>');

    $html = renderReportDocument([
        'issues' => collect([$issue]),
        'projectWideIssues' => collect([$issue]),
    ]);

    expect($html)
        ->toContain('Safe summary')
        ->not->toContain('<script>')
        ->not->toContain('alert(');
});

test('keeps issue cards concise and preserves full long descriptions in appendix', function () {
    $issue = reportDocumentIssue('issue-long-description', 'Detailed keyboard failure', 'major', 'open');
    $description = 'Short finding. '.str_repeat('Full technical context with reproduction steps and affected controls. ', 7);
    $issue->setAttribute('description', '<p>'.$description.'</p>');
    $issue->setAttribute('impact', '<p>Keyboard users cannot complete this task.</p>');
    $issue->setAttribute('solution_suggestions', '<p>Restore keyboard focus after the dialog closes.</p>');

    $html = renderReportDocument([
        'issues' => collect([$issue]),
        'projectWideIssues' => collect([$issue]),
    ]);

    $appendixPosition = strpos($html, 'id="full-descriptions-appendix"');
    $fullTextPosition = strpos($html, 'affected controls.', $appendixPosition);

    expect($appendixPosition)->not->toBeFalse()
        ->and($fullTextPosition)->not->toBeFalse()
        ->and($fullTextPosition)->toBeGreaterThan($appendixPosition)
        ->and($html)->toContain('Short finding.')
        ->toContain('Keyboard users cannot complete this task.')
        ->toContain('Restore keyboard focus after the dialog closes.')
        ->toContain(__('Full issue descriptions'))
        ->toContain('href="#full-descriptions-appendix"');
});
