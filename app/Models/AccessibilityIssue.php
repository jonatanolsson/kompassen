<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['project_id', 'page_id', 'title', 'description', 'severity', 'difficulty', 'component_area', 'sample_scope', 'status', 'screenshot_url', 'solution_suggestions', 'resolution_status', 'resolution_notes'])]
class AccessibilityIssue extends Model
{
    use HasFactory;

    protected $keyType = 'string';

    public $incrementing = false;

    protected static function boot(): void
    {
        parent::boot();
        static::creating(function (Model $model) {
            if (! $model->getKey()) {
                $model->{$model->getKeyName()} = \Illuminate\Support\Str::ulid();
            }
        });
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(AccessibilityProject::class, 'project_id');
    }

    public function page(): BelongsTo
    {
        return $this->belongsTo(AccessibilityPage::class, 'page_id');
    }

    public function wcagCriteria(): BelongsToMany
    {
        return $this->belongsToMany(
            WcagSuccessCriterion::class,
            'accessibility_issue_wcag_criterion',
            'accessibility_issue_id',
            'wcag_success_criterion_id'
        )->withPivot('failure_type', 'comment', 'code_snippet', 'screenshot_path')
            ->withTimestamps();
    }

    public function attachments(): HasMany
    {
        return $this->hasMany(AccessibilityIssueAttachment::class, 'accessibility_issue_id');
    }
}
