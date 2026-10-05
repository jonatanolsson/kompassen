<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

#[Fillable(['team_id', 'name', 'description', 'client_logo', 'target_wcag_level', 'target_wcag_version', 'audit_date', 'status', 'report_options', 'report_site_url'])]
class AccessibilityProject extends Model
{
    use HasFactory;

    public const WCAG_VERSIONS = ['2.0', '2.1', '2.2'];

    protected $attributes = [
        'target_wcag_version' => '2.1',
    ];

    protected $keyType = 'string';

    public $incrementing = false;

    protected function casts(): array
    {
        return [
            'audit_date' => 'datetime',
            'report_options' => 'array',
        ];
    }

    protected static function boot(): void
    {
        parent::boot();
        static::creating(function (Model $model) {
            if (! $model->getKey()) {
                $model->{$model->getKeyName()} = Str::ulid();
            }
        });
    }

    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class);
    }

    public function pages(): HasMany
    {
        return $this->hasMany(AccessibilityPage::class, 'project_id');
    }

    public function issues(): HasMany
    {
        return $this->hasMany(AccessibilityIssue::class, 'project_id');
    }

    public function members(): HasMany
    {
        return $this->hasMany(ProjectMember::class, 'project_id');
    }

    public function shareLinks(): HasMany
    {
        return $this->hasMany(ProjectShareLink::class, 'project_id');
    }

    public function reports(): HasMany
    {
        return $this->hasMany(AccessibilityReport::class, 'project_id');
    }

    public function methodologies(): BelongsToMany
    {
        return $this->belongsToMany(TestingMethodology::class, 'accessibility_project_methodologies', 'project_id', 'methodology_id')
            ->withPivot('notes')
            ->withTimestamps();
    }

    public function getDomainsAttribute(): array
    {
        if (! $this->relationLoaded('pages')) {
            return [];
        }

        $domains = $this->pages
            ->pluck('url')
            ->filter()
            ->map(function (string $url): ?string {
                $host = parse_url($url, PHP_URL_HOST);

                return is_string($host) ? preg_replace('/^www\./', '', $host) : null;
            })
            ->filter()
            ->unique();

        $primaryHost = parse_url($this->report_site_url ?? '', PHP_URL_HOST);
        if (is_string($primaryHost)) {
            $domains->prepend(preg_replace('/^www\./', '', $primaryHost));
        }

        return $domains->unique()->values()->all();
    }
}
