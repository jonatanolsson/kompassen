<!DOCTYPE html>
<html lang="en"> {{ __('') }} <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title> {{ __('') }} </style>
</head> {{ __('') }} <div class="container">
            <header role="banner">
                @if ($project-> {{ __('') }} </div>
                @endif
                <h1> {{ __('') }} </p> {{ __('') }} <div class="stat">
                    <div class="stat-value">{{ $issues->count() }}</div>
                    <div class="stat-label">{{ __('Total Issues') }}</div> {{ __('') }} <div class="stat-value">{{ $counts['critical'] ?? 0 }}</div>
                    <div class="stat-label">{{ __('Critical') }}</div> {{ __('') }} <div class="stat-value">{{ $counts['major'] ?? 0 }}</div>
                    <div class="stat-label">{{ __('Major') }}</div> {{ __('') }} <div class="stat-value">{{ $project->target_wcag_level }}</div>
                    <div class="stat-label">{{ __('Target Level') }}</div> {{ __('') }} <section class="content" role="main">
                @if ($scope)
                    <section class="scope" aria-label="Audit Scope">
                        <h2 class="scope-title">{{ __('Scope') }}</h2>
                        <p> {{ __('') }} <div role="region" aria-label="Issues by WCAG Success Criteria">
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

                            <ul class="issue-list" role="list"> {{ __('') }} <h3 class="issue-heading">{{ $issue-> {{ __('') }} </span>
                                        </div>
                                        @if ($issue->description)
                                            <div class="issue-description"> {{ __('description !!}') }} </li> {{ __('') }} <p style="color: #6b7280; text-align: center; padding: 40px 0;">{{ __('No issues found in this project.') }}</p> {{ __('') }} <footer role="contentinfo">
                <p>Report generated on {{ now()->format('M d, Y') }} at {{ now()-> {{ __('') }} </div>
    </main>
</body>
</html>
