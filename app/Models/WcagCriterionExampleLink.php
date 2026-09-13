<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class WcagCriterionExampleLink extends Model
{
    protected $fillable = [
        'wcag_criterion_example_id',
        'url',
        'label',
        'sort_order',
    ];

    protected $keyType = 'string';

    public $incrementing = false;

    protected static function boot(): void
    {
        parent::boot();

        static::creating(function (Model $model): void {
            if (! $model->getKey()) {
                $model->{$model->getKeyName()} = Str::ulid();
            }
        });
    }

    public function example(): BelongsTo
    {
        return $this->belongsTo(WcagCriterionExample::class, 'wcag_criterion_example_id');
    }
}
