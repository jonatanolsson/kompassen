<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['number', 'level', 'name_en', 'name_sv', 'description_en', 'description_sv', 'url'])]
class WcagSuccessCriterion extends Model
{
    use HasFactory;

    protected $keyType = 'string';

    public $incrementing = false;

    protected $appends = ['code'];

    protected static function boot(): void
    {
        parent::boot();
        static::creating(function (Model $model) {
            if (! $model->getKey()) {
                $model->{$model->getKeyName()} = \Illuminate\Support\Str::ulid();
            }
        });
    }

    public function issues(): BelongsToMany
    {
        return $this->belongsToMany(
            AccessibilityIssue::class,
            'accessibility_issue_wcag_criterion',
            'wcag_success_criterion_id',
            'accessibility_issue_id'
        );
    }

    public function examples(): HasMany
    {
        return $this->hasMany(WcagCriterionExample::class, 'wcag_success_criterion_id')
            ->orderBy('sort_order')
            ->orderBy('created_at');
    }

    public function getCodeAttribute(): string
    {
        return $this->number;
    }

    public function getNameAttribute(): string
    {
        $locale = app()->getLocale();

        return match ($locale) {
            'sv' => $this->name_sv,
            default => $this->name_en,
        };
    }

    public function getDescriptionAttribute(): string
    {
        $locale = app()->getLocale();

        return match ($locale) {
            'sv' => $this->description_sv,
            default => $this->description_en,
        };
    }
}
