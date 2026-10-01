<?php

namespace App\Services;

use App\Models\AccessibilityProject;
use DOMDocument;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Illuminate\Validation\ValidationException;

class ReportSiteIcon
{
    private const MAX_BYTES = 1048576;

    public static function primarySiteRules(): array
    {
        return ['nullable', 'url', 'max:255', function (string $attribute, mixed $value, \Closure $fail): void {
            if (self::origin($value) === null) {
                $fail(__('Enter a public HTTPS website address, for example https://example.com.'));
            }
        }];
    }

    /**
     * @return array<string, string>
     */
    public static function sitesFor(AccessibilityProject $project): array
    {
        return $project->pages()->where('scope', 'in_scope')->pluck('url')
            ->prepend($project->report_site_url)
            ->map(fn (?string $url): ?string => self::origin($url))
            ->filter()
            ->unique()
            ->sort()
            ->mapWithKeys(fn (string $origin): array => [$origin => parse_url($origin, PHP_URL_HOST)])
            ->all();
    }

    public function fetch(AccessibilityProject $project, string $siteUrl): string
    {
        if (! array_key_exists($siteUrl, self::sitesFor($project))) {
            $this->fail(__('Choose a website from this project before generating the report.'));
        }

        $host = parse_url($siteUrl, PHP_URL_HOST);
        $address = $this->publicAddress($host);
        $page = $this->request($siteUrl.'/', $host, $address);

        if (! $page->successful() || ! str_contains(strtolower($page->header('Content-Type', '')), 'text/html')) {
            $this->fail(__('The selected website could not be read. Check its address or choose no website icon.'));
        }

        $document = new DOMDocument;
        $previous = libxml_use_internal_errors(true);
        try {
            $document->loadHTML($page->body(), LIBXML_NONET);
        } finally {
            libxml_clear_errors();
            libxml_use_internal_errors($previous);
        }

        $paths = [];
        foreach ($document->getElementsByTagName('link') as $link) {
            if (! preg_match('/\bicon\b/i', $link->getAttribute('rel'))) {
                continue;
            }

            $href = trim($link->getAttribute('href'));
            if ($href === '' || str_starts_with($href, '//')) {
                continue;
            }

            $url = str_starts_with($href, '/')
                ? $siteUrl.$href
                : (str_starts_with($href, 'https://') ? $href : $siteUrl.'/'.ltrim($href, './'));
            if (self::origin($url) === $siteUrl && ! str_contains($url, '#')) {
                $paths[] = $url;
            }
        }

        $paths[] = $siteUrl.'/favicon.ico';
        foreach (array_unique($paths) as $url) {
            $response = $this->request($url, $host, $address);
            if (! $response->successful()) {
                continue;
            }

            $bytes = $response->body();
            if (str_starts_with($bytes, "\x00\x00\x01\x00")) {
                $bytes = $this->embeddedPng($bytes) ?? '';
            }

            $mime = $bytes !== '' ? (new \finfo(FILEINFO_MIME_TYPE))->buffer($bytes) : false;
            if (in_array($mime, ['image/png', 'image/jpeg', 'image/gif', 'image/webp'], true)
                && getimagesizefromstring($bytes) !== false) {
                return 'data:'.$mime.';base64,'.base64_encode($bytes);
            }
        }

        $this->fail(__('No supported website icon was found. Choose another website or choose no website icon.'));
    }

    public static function origin(?string $url): ?string
    {
        if (! is_string($url)) {
            return null;
        }

        $parts = parse_url($url);
        if ($parts === false || strtolower($parts['scheme'] ?? '') !== 'https'
            || ! empty($parts['user']) || ! empty($parts['pass'])
            || isset($parts['port']) && $parts['port'] !== 443) {
            return null;
        }

        $host = strtolower($parts['host'] ?? '');
        if (! preg_match('/^(?=.{1,253}$)[a-z0-9-]+(?:\.[a-z0-9-]+)+$/D', $host)) {
            return null;
        }

        return 'https://'.$host;
    }

    protected function publicAddress(string $host): string
    {
        $addresses = gethostbynamel($host);
        if ($addresses === false || $addresses === []) {
            $this->fail(__('The selected website could not be resolved. Check its address or choose no website icon.'));
        }

        foreach ($addresses as $address) {
            if (! filter_var($address, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4 | FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) {
                $this->fail(__('The selected website is not publicly accessible. Choose another website or no website icon.'));
            }
        }

        return $addresses[0];
    }

    private function request(string $url, string $host, string $address): Response
    {
        try {
            $response = Http::withOptions([
                'allow_redirects' => false,
                'proxy' => '',
                'curl' => [
                    CURLOPT_RESOLVE => ["{$host}:443:{$address}"],
                    CURLOPT_IPRESOLVE => CURL_IPRESOLVE_V4,
                    CURLOPT_PROXY => '',
                    CURLOPT_NOPROGRESS => false,
                    CURLOPT_PROGRESSFUNCTION => static fn ($resource, $downloadSize, $downloaded): int => $downloaded > self::MAX_BYTES ? 1 : 0,
                ],
            ])->connectTimeout(3)->timeout(8)->get($url);
        } catch (ConnectionException $exception) {
            throw ValidationException::withMessages([
                'report' => __('The website icon could not be retrieved. Try again or choose no website icon.'),
            ]);
        }

        if (strlen($response->body()) > self::MAX_BYTES) {
            $this->fail(__('The website icon is too large. Choose no website icon.'));
        }

        return $response;
    }

    private function embeddedPng(string $icon): ?string
    {
        if (strlen($icon) < 22) {
            return null;
        }

        $count = unpack('v', substr($icon, 4, 2))[1];
        for ($i = 0; $i < $count && $i < 32; $i++) {
            $entry = substr($icon, 6 + $i * 16, 16);
            if (strlen($entry) !== 16) {
                break;
            }
            $size = unpack('V', substr($entry, 8, 4))[1];
            $offset = unpack('V', substr($entry, 12, 4))[1];
            $image = substr($icon, $offset, $size);
            if ($size <= self::MAX_BYTES && strlen($image) === $size && str_starts_with($image, "\x89PNG\r\n\x1a\n")) {
                return $image;
            }
        }

        return null;
    }

    private function fail(string $message): never
    {
        throw ValidationException::withMessages(['report' => $message]);
    }
}
