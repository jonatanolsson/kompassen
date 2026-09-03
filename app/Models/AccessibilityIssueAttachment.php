<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['accessibility_issue_id', 'filename', 'original_filename', 'mime_type', 'size', 'path'])]
class AccessibilityIssueAttachment extends Model
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

    public function issue(): BelongsTo
    {
        return $this->belongsTo(AccessibilityIssue::class, 'accessibility_issue_id');
    }
}
