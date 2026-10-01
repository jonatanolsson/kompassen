<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AccessibilityReport extends Model
{
    use HasUlids;

    protected $fillable = [
        'project_id',
        'title',
        'scope',
        'target_wcag_level',
        'total_issues',
        'critical_count',
        'major_count',
        'moderate_count',
        'minor_count',
        'html_content',
        'created_by',
        'series_id',
        'version',
        'previous_version_id',
        'generated_at',
        'audit_date_snapshot',
        'client_logo_snapshot',
        'report_options',
        'document_metadata',
    ];

    protected function casts(): array
    {
        return [
            'report_options' => 'array',
            'document_metadata' => 'array',
            'generated_at' => 'datetime',
            'audit_date_snapshot' => 'datetime',
        ];
    }

    public function previousVersion(): BelongsTo
    {
        return $this->belongsTo(self::class, 'previous_version_id');
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(AccessibilityProject::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
