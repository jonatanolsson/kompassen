<?php

namespace App\Models;

use App\Services\IssueDiagramSvg;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class WcagCriterionExample extends Model
{
    protected $fillable = [
        'wcag_success_criterion_id',
        'team_id',
        'title',
        'description',
        'code_snippet',
        'code_language',
        'mermaid_svg',
        'mermaid_source_hash',
        'url',
        'url_label',
        'sort_order',
    ];

    protected $keyType = 'string';

    public $incrementing = false;

    protected static function boot(): void
    {
        parent::boot();
        static::creating(function (Model $model) {
            if (! $model->getKey()) {
                $model->{$model->getKeyName()} = Str::ulid();
            }
        });
        static::saving(function (self $example): void {
            if ($example->code_language !== 'mermaid'
                || ($example->isDirty(['code_snippet', 'code_language', 'mermaid_svg', 'mermaid_source_hash'])
                    && ! IssueDiagramSvg::isCurrent($example->code_snippet, $example->mermaid_svg, $example->mermaid_source_hash))) {
                $example->mermaid_svg = null;
                $example->mermaid_source_hash = null;
            }
        });
    }

    public function criterion(): BelongsTo
    {
        return $this->belongsTo(WcagSuccessCriterion::class, 'wcag_success_criterion_id');
    }

    public function scopeVisibleToTeam(Builder $query, string $teamId): Builder
    {
        $column = $query->qualifyColumn('team_id');

        return $query->where(fn (Builder $query): Builder => $query
            ->whereNull($column)
            ->orWhere($column, $teamId));
    }

    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class);
    }

    public function referenceLinks(): HasMany
    {
        return $this->hasMany(WcagCriterionExampleLink::class, 'wcag_criterion_example_id')
            ->orderBy('sort_order')
            ->orderBy('id');
    }

    public function attachments(): HasMany
    {
        return $this->hasMany(WcagCriterionExampleAttachment::class, 'wcag_criterion_example_id')
            ->orderBy('sort_order')
            ->orderBy('id');
    }

    public function issues(): BelongsToMany
    {
        return $this->belongsToMany(
            AccessibilityIssue::class,
            'accessibility_issue_example',
            'wcag_criterion_example_id',
            'accessibility_issue_id'
        )->withPivot('sort_order');
    }
}
