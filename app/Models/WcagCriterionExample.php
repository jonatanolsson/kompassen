<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class WcagCriterionExample extends Model
{
    protected $fillable = [
        'wcag_success_criterion_id',
        'title',
        'description',
        'code_snippet',
        'code_language',
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
    }

    public function criterion(): BelongsTo
    {
        return $this->belongsTo(WcagSuccessCriterion::class, 'wcag_success_criterion_id');
    }

    public function referenceLinks(): HasMany
    {
        return $this->hasMany(WcagCriterionExampleLink::class, 'wcag_criterion_example_id')
            ->orderBy('sort_order')
            ->orderBy('id');
    }
}
