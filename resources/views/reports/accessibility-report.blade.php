<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title }} - {{ __('Accessibility Audit Report') }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
            line-height: 1.6;
            color: #1f2937;
            background-color: #f9fafb;
            padding: 40px 20px;
        }
        main {
            max-width: 900px;
            margin: 0 auto;
        }
        .container {
            background-color: white;
            border-radius: 8px;
            box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.1);
            overflow: hidden;
        }
        header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 40px;
            text-align: center;
        }
        .header-logo {
            margin-bottom: 16px;
        }
        .header-logo img {
            max-height: 56px;
            max-width: 200px;
            object-fit: contain;
            filter: brightness(0) invert(1);
            opacity: 0.95;
        }
        header h1 {
            font-size: 32px;
            font-weight: 700;
            margin-bottom: 10px;
        }
        header p {
            font-size: 16px;
            opacity: 0.95;
        }
        .summary {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(120px, 1fr));
            gap: 20px;
            padding: 30px 40px;
            background-color: #f3f4f6;
            border-bottom: 1px solid #e5e7eb;
        }
        .stat {
            text-align: center;
        }
        .stat-value {
            font-size: 28px;
            font-weight: 700;
            color: #1f2937;
        }
        .stat-label {
            font-size: 12px;
            color: #6b7280;
            text-transform: uppercase;
            margin-top: 5px;
            font-weight: 600;
        }
        .content {
            padding: 40px;
        }
        .scope {
            background-color: #f0f9ff;
            border-left: 4px solid #0284c7;
            padding: 15px;
            border-radius: 4px;
            margin-bottom: 30px;
        }
        .targets {
            margin-bottom: 30px;
        }
        .target {
            padding: 12px 0;
            border-bottom: 1px solid #e5e7eb;
        }
        .target:last-child {
            border-bottom: none;
        }
        .target-name {
            font-weight: 600;
            color: #1f2937;
        }
        .target-meta {
            color: #6b7280;
            font-size: 13px;
            margin-top: 2px;
        }
        .scope-title {
            font-weight: 600;
            color: #0284c7;
            margin-bottom: 5px;
            font-size: 14px;
        }
        .wcag-criterion {
            background-color: #f9fafb;
            border: 1px solid #e5e7eb;
            border-radius: 6px;
            padding: 20px;
            margin-bottom: 20px;
        }
        .criterion-header {
            font-weight: 600;
            color: #1f2937;
            margin-bottom: 10px;
            font-size: 18px;
        }
        .criterion-description {
            color: #6b7280;
            font-size: 14px;
            margin-bottom: 15px;
            line-height: 1.5;
        }
        .issue-list {
            list-style: none;
        }
        .issue {
            padding: 12px 0;
            border-bottom: 1px solid #e5e7eb;
        }
        .issue:last-child {
            border-bottom: none;
        }
        .issue-title {
            margin-bottom: 6px;
        }
        .issue-heading {
            font-size: 1.125rem;
            font-weight: 700;
            color: #111827;
            margin-bottom: 4px;
            line-height: 1.4;
        }
        .severity-badge {
            display: inline-block;
            padding: 3px 8px;
            border-radius: 4px;
            font-size: 12px;
            font-weight: 600;
            margin-right: 8px;
            text-transform: uppercase;
        }
        .severity-critical {
            background-color: #fee2e2;
            color: #991b1b;
        }
        .severity-major {
            background-color: #fecaca;
            color: #7f1d1d;
        }
        .severity-moderate {
            background-color: #fef08a;
            color: #854d0e;
        }
        .severity-minor {
            background-color: #dbeafe;
            color: #1e3a8a;
        }
        .issue-description {
            margin-top: 8px;
            padding-top: 8px;
            border-top: 1px solid #f3f4f6;
            color: #6b7280;
            font-size: 14px;
            line-height: 1.6;
        }
        .issue-description h1, .issue-description h2, .issue-description h3,
        .issue-description h4, .issue-description h5, .issue-description h6 {
            color: #1f2937;
            font-weight: 600;
            margin-top: 0.75em;
            margin-bottom: 0.25em;
        }
        .issue-description h1 { font-size: 1.25em; }
        .issue-description h2 { font-size: 1.125em; }
        .issue-description h3, .issue-description h4 { font-size: 1em; }
        .issue-description p { margin-bottom: 0.5em; }
        .issue-description ul, .issue-description ol { padding-left: 1.4em; margin-bottom: 0.5em; }
        .issue-description li { margin-bottom: 0.2em; }
        .issue-description strong { font-weight: 600; color: #374151; }
        .issue-description a { color: #374151; text-decoration: underline; }
        .issue-description code { font-family: monospace; background: #f3f4f6; padding: 0.1em 0.3em; border-radius: 3px; font-size: 0.875em; }
        .issue-description blockquote { border-left: 3px solid #e5e7eb; padding-left: 0.75em; color: #9ca3af; margin-left: 0; }
        footer {
            background-color: #f3f4f6;
            padding: 20px 40px;
            border-top: 1px solid #e5e7eb;
            font-size: 13px;
            color: #6b7280;
            text-align: center;
        }
        @media (max-width: 640px) {
            header {
                padding: 20px;
            }
            header h1 {
                font-size: 24px;
            }
            .content {
                padding: 20px;
            }
            .summary {
                padding: 20px;
            }
        }
    </style>
</head>
<body>
    <main>
        <div class="container">
            <header role="banner">
                @if ($project->client_logo)
                    <div class="header-logo">
                        <img src="{{ Storage::url($project->client_logo) }}" alt="{{ __(':name logo', ['name' => $project->name]) }}" />
                    </div>
                @endif
                <h1>{{ $title }}</h1>
                <p>{{ $project->team->name }}</p>
            </header>

            <div class="summary" role="region" aria-label="{{ __('Report Summary') }}">
                <div class="stat">
                    <div class="stat-value">{{ $issues->count() }}</div>
                    <div class="stat-label">{{ __('Total Issues') }}</div>
                </div>
                <div class="stat">
                    <div class="stat-value">{{ $counts['critical'] ?? 0 }}</div>
                    <div class="stat-label">{{ __('Critical') }}</div>
                </div>
                <div class="stat">
                    <div class="stat-value">{{ $counts['major'] ?? 0 }}</div>
                    <div class="stat-label">{{ __('Major') }}</div>
                </div>
                <div class="stat">
                    <div class="stat-value">{{ $project->target_wcag_level }}</div>
                    <div class="stat-label">{{ __('Target Level') }}</div>
                </div>
            </div>

            <section class="content" role="main">
                @if ($scope)
                    <section class="scope" aria-label="{{ __('Audit Scope') }}">
                        <h2 class="scope-title">{{ __('Scope') }}</h2>
                        <p>{{ $scope }}</p>
                    </section>
                @endif

                @if ($project->pages->isNotEmpty())
                    <section class="targets" aria-labelledby="targets-heading">
                        <h2 id="targets-heading" class="criterion-header">{{ __('Pages & Services') }}</h2>
                        @foreach ($project->pages as $page)
                            @php
                                $resourceType = $page->resource_type ?? 'page';
                                $accessContext = $page->access_context ?? 'not_applicable';
                            @endphp
                            <div class="target">
                                <div class="target-name">
                                    {{ $page->name }}
                                    ({{ __($resourceType === 'service' ? 'Service' : 'Page') }})
                                </div>
                                @if ($page->url)
                                    <div class="target-meta">{{ $page->url }}</div>
                                @endif
                                <div class="target-meta">
                                    {{ __($page->scope === 'in_scope' ? 'In Scope' : 'Out of Scope') }}
                                    @if ($accessContext !== 'not_applicable')
                                        · {{ __($accessContext === 'authenticated' ? 'Authenticated' : ($accessContext === 'mixed' ? 'Public and authenticated' : 'Public')) }}
                                    @endif
                                </div>
                                @if ($page->description)
                                    <div class="issue-description">{!! \App\Helpers\MarkdownHelper::toHtml($page->description) !!}</div>
                                @endif
                            </div>
                        @endforeach
                    </section>
                @endif

                <div role="region" aria-label="{{ __('Issues by WCAG Success Criteria') }}">
                    @forelse ($issuesByWcag as $key => $wcagGroup)
                        <section class="wcag-criterion">
                            @if ($wcagGroup['sc'])
                                <h2 class="criterion-header">{{ $wcagGroup['sc']->number }}: {{ $wcagGroup['sc']->name }}</h2>
                                @if ($wcagGroup['sc']->description)
                                    <p class="criterion-description">{{ $wcagGroup['sc']->description }}</p>
                                @endif
                            @else
                                <h2 class="criterion-header">{{ __('Other Issues') }}</h2>
                            @endif

                            <ul class="issue-list" role="list">
                                @foreach ($wcagGroup['issues'] as $issue)
                                    <li class="issue" role="listitem">
                                        <div class="issue-title">
                                            <h3 class="issue-heading">{{ $issue->title }}</h3>
                                            <span class="severity-badge severity-{{ strtolower($issue->severity) }}" aria-label="{{ __('Severity') }}: {{ __(Str::title(str_replace(['_', '-'], ' ', $issue->severity))) }}">
                                                {{ __(Str::title(str_replace(['_', '-'], ' ', $issue->severity))) }}
                                            </span>
                                        </div>
                                        @if ($issue->description)
                                            <div class="issue-description">{!! \App\Helpers\MarkdownHelper::toHtml($issue->description) !!}</div>
                                        @endif
                                        <p class="issue-description">
                                            <strong>{{ __('Status') }}:</strong>
                                            {{ __(Str::title(str_replace(['_', '-'], ' ', $issue->status))) }}
                                        </p>
                                        @if ($issue->resolution_notes)
                                            <div class="issue-description">
                                                <strong>{{ __('Resolution Notes') }}:</strong>
                                                {{ $issue->resolution_notes }}
                                            </div>
                                        @endif
                                    </li>
                                @endforeach
                            </ul>
                        </section>
                    @empty
                    <p style="color: #6b7280; text-align: center; padding: 40px 0;">{{ __('No issues found in this project.') }}</p>
                    @endforelse
                </div>
            </section>

            <footer role="contentinfo">
                <p>{{ __('Report generated on :date', ['date' => now()->format('Y-m-d H:i')]) }}</p>
            </footer>
        </div>
    </main>
</body>
</html>
