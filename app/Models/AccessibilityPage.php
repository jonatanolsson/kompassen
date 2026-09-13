<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

#[Fillable(['project_id', 'name', 'resource_type', 'url', 'access_context', 'description', 'scope'])]
class AccessibilityPage extends Model
{
    use HasFactory;

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

    public function project(): BelongsTo
    {
        return $this->belongsTo(AccessibilityProject::class, 'project_id');
    }

    public function issues(): HasMany
    {
        return $this->hasMany(AccessibilityIssue::class, 'page_id');
    }
}
