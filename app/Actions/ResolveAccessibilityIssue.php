<?php

namespace App\Actions;

use App\Models\AccessibilityIssue;

class ResolveAccessibilityIssue
{
    public function __invoke(AccessibilityIssue $issue, string $status, ?string $notes = null): void
    {
        $issue->update([
            'resolution_status' => $status,
            'resolution_notes' => $notes,
            'resolved_at' => $status === 'open' ? null : now(),
        ]);
    }
}
