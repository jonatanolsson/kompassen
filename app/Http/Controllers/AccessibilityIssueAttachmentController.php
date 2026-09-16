<?php

namespace App\Http\Controllers;

use App\Models\AccessibilityIssue;
use App\Models\AccessibilityIssueAttachment;
use App\Models\AccessibilityProject;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Storage;

class AccessibilityIssueAttachmentController extends Controller
{
    use AuthorizesRequests;

    public function destroy(AccessibilityProject $project, AccessibilityIssue $issue, AccessibilityIssueAttachment $attachment)
    {
        $this->authorize('update', $project);

        Storage::disk('public')->delete($attachment->path);
        $attachment->delete();

        return back()->with('success', __('Image deleted successfully.'));
    }
}
