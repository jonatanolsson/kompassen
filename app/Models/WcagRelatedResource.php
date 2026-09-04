<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class WcagRelatedResource extends Model
{
    protected $fillable = [
        'wcag_success_criterion_id',
        'type',
        'code',
        'title_en',
        'title_sv',
        'description_en',
        'description_sv',
        'url',
        'order',
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

    public function wcagSuccessCriterion(): BelongsTo
    {
        return $this->belongsTo(WcagSuccessCriterion::class);
    }
}
