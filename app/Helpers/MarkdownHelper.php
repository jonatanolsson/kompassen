<?php

namespace App\Helpers;

use League\CommonMark\CommonMarkConverter;
use Stevebauman\Purify\Facades\Purify;

class MarkdownHelper
{
    public static function toHtml(?string $content): string
    {
        if (! $content) {
            return '';
        }

        $content = trim($content);
        $html = self::containsHtml($content)
            ? $content
            : (new CommonMarkConverter)->convert($content)->getContent();

        return Purify::clean($html);
    }

    private static function containsHtml(string $content): bool
    {
        return preg_match('/<\s*\/?\s*(p|h[1-6]|ul|ol|li|strong|em|a|blockquote|pre|code|br|div|span|img|table|thead|tbody|tr|th|td)\b/i', $content) === 1;
    }
}
