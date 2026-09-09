<?php

namespace App\Helpers;

use League\CommonMark\CommonMarkConverter;
use Stevebauman\Purify\Facades\Purify;

class MarkdownHelper
{
    public static function toHtml(?string $markdown): string
    {
        if (! $markdown) {
            return '';
        }

        $converter = new CommonMarkConverter();
        $html = $converter->convert($markdown)->getContent();

        // Sanitize HTML to prevent XSS
        return Purify::clean($html);
    }
}
