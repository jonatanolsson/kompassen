<?php

namespace App\Providers;

use App\Models\AccessibilityProject;
use App\Models\TestingMethodology;
use App\Models\WcagCriterionExample;
use App\Models\WcagSuccessCriterion;
use App\Policies\AccessibilityProjectPolicy;
use App\Policies\TestingMethodologyPolicy;
use App\Policies\WcagCriterionExamplePolicy;
use App\Policies\WcagSuccessCriterionPolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        AccessibilityProject::class => AccessibilityProjectPolicy::class,
        TestingMethodology::class => TestingMethodologyPolicy::class,
        WcagCriterionExample::class => WcagCriterionExamplePolicy::class,
        WcagSuccessCriterion::class => WcagSuccessCriterionPolicy::class,
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        //
    }
}
