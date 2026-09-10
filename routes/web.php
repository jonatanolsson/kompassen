<?php

use App\Http\Controllers\AccessibilityIssueAttachmentController;
use App\Http\Controllers\AccessibilityIssueController;
use App\Http\Controllers\AccessibilityPageController;
use App\Http\Controllers\AccessibilityProjectController;
use App\Http\Controllers\AccessibilityReportController;
use App\Http\Controllers\GuestProjectController;
use App\Http\Controllers\ProjectMemberController;
use App\Http\Controllers\ProjectShareLinkController;
use App\Livewire\AppSettings;
use App\Livewire\CreateAccessibilityIssue;
use App\Livewire\CreateAccessibilityPage;
use App\Livewire\CreateAccessibilityProject;
use App\Livewire\Dashboard;
use App\Livewire\EditAccessibilityIssue;
use App\Livewire\EditAccessibilityProject;
use App\Livewire\TestingMethodologies;
use App\Livewire\WcagKnowledgeBase;
use Illuminate\Support\Facades\Route;

Route::view('/', 'welcome');

Route::get('dashboard', Dashboard::class)
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::view('profile', 'profile')
    ->middleware(['auth'])
    ->name('profile');

Route::get('settings', AppSettings::class)
    ->middleware(['auth'])
    ->name('settings');

Route::get('wcag-knowledge-base', WcagKnowledgeBase::class)
    ->middleware(['auth'])
    ->name('wcag.knowledge-base');

Route::get('testing-methodologies', TestingMethodologies::class)
    ->middleware(['auth'])
    ->name('testing-methodologies');

// Guest project sharing (no auth required)
Route::get('projects/shared/{token}', [GuestProjectController::class, 'show'])
    ->name('projects.shared');

// Accessibility reporting routes
Route::middleware(['auth'])->group(function () {
    // Projects
    Route::get('accessibility-projects', [AccessibilityProjectController::class, 'index'])
        ->name('accessibility-projects.index');
    Route::get('accessibility-projects/create', CreateAccessibilityProject::class)
        ->name('accessibility-projects.create');
    Route::post('accessibility-projects', [AccessibilityProjectController::class, 'store'])
        ->name('accessibility-projects.store');
    Route::get('accessibility-projects/{project}', [AccessibilityProjectController::class, 'show'])
        ->name('accessibility-projects.show');
    Route::get('accessibility-projects/{project}/edit', EditAccessibilityProject::class)
        ->name('accessibility-projects.edit');
    Route::put('accessibility-projects/{project}', [AccessibilityProjectController::class, 'update'])
        ->name('accessibility-projects.update');
    Route::delete('accessibility-projects/{project}', [AccessibilityProjectController::class, 'destroy'])
        ->name('accessibility-projects.destroy');

    // Project members
    Route::post('accessibility-projects/{project}/members', [ProjectMemberController::class, 'store'])
        ->name('project-members.store');
    Route::put('accessibility-projects/{project}/members/{member}', [ProjectMemberController::class, 'update'])
        ->name('project-members.update');
    Route::delete('accessibility-projects/{project}/members/{member}', [ProjectMemberController::class, 'destroy'])
        ->name('project-members.destroy');

    // Project share links
    Route::post('accessibility-projects/{project}/share-links', [ProjectShareLinkController::class, 'store'])
        ->name('project-share-links.store');
    Route::delete('accessibility-projects/{project}/share-links/{link}', [ProjectShareLinkController::class, 'destroy'])
        ->name('project-share-links.destroy');

    // Project preview (authenticated owner sees guest view)
    Route::get('accessibility-projects/{project}/preview', [GuestProjectController::class, 'preview'])
        ->name('accessibility-projects.preview');

    // Reports
    Route::get('accessibility-projects/{project}/reports', [AccessibilityReportController::class, 'index'])
        ->name('accessibility-reports.index');
    Route::get('accessibility-projects/{project}/reports/create', [AccessibilityReportController::class, 'create'])
        ->name('accessibility-reports.create');
    Route::post('accessibility-projects/{project}/reports', [AccessibilityReportController::class, 'store'])
        ->name('accessibility-reports.store');
    Route::get('accessibility-projects/{project}/reports/{report}', [AccessibilityReportController::class, 'show'])
        ->name('accessibility-reports.show');
    Route::get('accessibility-projects/{project}/reports/{report}/download', [AccessibilityReportController::class, 'download'])
        ->name('accessibility-reports.download');
    Route::delete('accessibility-projects/{project}/reports/{report}', [AccessibilityReportController::class, 'destroy'])
        ->name('accessibility-reports.destroy');

    // Pages within projects
    Route::get('accessibility-projects/{project}/pages/create', CreateAccessibilityPage::class)
        ->name('accessibility-pages.create');
    Route::post('accessibility-projects/{project}/pages', [AccessibilityPageController::class, 'store'])
        ->name('accessibility-pages.store');
    Route::get('accessibility-projects/{project}/pages/{page}/edit', [AccessibilityPageController::class, 'edit'])
        ->name('accessibility-pages.edit');
    Route::put('accessibility-projects/{project}/pages/{page}', [AccessibilityPageController::class, 'update'])
        ->name('accessibility-pages.update');
    Route::delete('accessibility-projects/{project}/pages/{page}', [AccessibilityPageController::class, 'destroy'])
        ->name('accessibility-pages.destroy');

    // Issues within projects
    Route::get('accessibility-projects/{project}/issues/create', CreateAccessibilityIssue::class)
        ->name('accessibility-issues.create');
    Route::post('accessibility-projects/{project}/issues', [AccessibilityIssueController::class, 'store'])
        ->name('accessibility-issues.store');
    Route::get('accessibility-projects/{project}/issues/{issue}', [AccessibilityIssueController::class, 'show'])
        ->name('accessibility-issues.show');
    Route::get('accessibility-projects/{project}/issues/{issue}/edit', EditAccessibilityIssue::class)
        ->name('accessibility-issues.edit');
    Route::delete('accessibility-projects/{project}/issues/{issue}', [AccessibilityIssueController::class, 'destroy'])
        ->name('accessibility-issues.destroy');
    Route::put('accessibility-projects/{project}/issues/{issue}/assign', [AccessibilityIssueController::class, 'assign'])
        ->name('accessibility-issues.assign');
    Route::get('accessibility-projects/{project}/issues/{issue}/export', [AccessibilityIssueController::class, 'export'])
        ->name('accessibility-issues.export');

    // Issue attachments
    Route::delete('accessibility-projects/{project}/issues/{issue}/attachments/{attachment}', [AccessibilityIssueAttachmentController::class, 'destroy'])
        ->name('accessibility-issue-attachments.destroy');

    // Issue resolution
    Route::patch('accessibility-projects/{project}/issues/{issue}/resolve', [AccessibilityIssueController::class, 'resolve'])
        ->name('accessibility-issues.resolve');
});

require __DIR__.'/auth.php';
