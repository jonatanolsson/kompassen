<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

#[Fillable([
    'wcag_criterion_example_id',
    'filename',
    'original_filename',
    'mime_type',
    'size',
    'path',
    'alt_text',
    'sort_order',
])]
class WcagCriterionExampleAttachment extends Model
{
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
