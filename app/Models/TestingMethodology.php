<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Str;

class TestingMethodology extends Model
{
    protected $keyType = 'string';

    public $incrementing = false;

    protected $fillable = [
        'name',
        'category',
        'description',
        'is_custom',
    ];

    protected $casts = [
        'is_custom' => 'boolean',
    ];

    protected static function boot(): void
    {
        parent::boot();

        static::creating(function (TestingMethodology $model) {
            if (empty($model->id)) {
                $model->id = (string) Str::ulid();
            }
        });
    }

    public function projects(): BelongsToMany
    {
        return $this->belongsToMany(AccessibilityProject::class, 'accessibility_project_methodologies', 'methodology_id', 'project_id')
            ->withPivot('notes')
            ->withTimestamps();
    }

    public function getCategoryLabelAttribute(): string
    {
        return match ($this->category) {
            'screen_reader' => __('Screen Reader'),
            'browser' => __('Browser'),
            'browser_extension' => __('Browser Extension'),
            'device' => __('Device'),
            'testing_tool' => __('Testing Tool'),
            default => __(ucfirst(str_replace('_', ' ', $this->category))),
        };
    }
}
