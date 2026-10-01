<?php

namespace App\Services;

use App\Models\AccessibilityIssue;
use App\Models\WcagCriterionExample;
use DOMAttr;
use DOMDocument;
use DOMElement;
use DOMNode;
use Illuminate\Validation\ValidationException;

class IssueDiagramSvg
{
    public const SVG_NAMESPACE = 'http://www.w3.org/2000/svg';

    public const MAX_SVG_BYTES = 512000;

    private const ID_PATTERN = '/^[A-Za-z][A-Za-z0-9_\-:.]{0,127}$/';

    private const LOCAL_REFERENCE_PATTERN = '/^url\(#([A-Za-z][A-Za-z0-9_\-:.]{0,127})\)$/';

    private const REFERENCE_ATTRIBUTES = ['fill', 'stroke', 'clip-path', 'marker-start', 'marker-mid', 'marker-end'];

    private const TEXT_ELEMENTS = ['text', 'tspan', 'title', 'desc'];

    /**
     * Element-specific attribute allowlist. Keep in sync with the browser capture in resources/js/app.js.
     *
     * @var array<string, list<string>>
     */
    private const ELEMENT_ATTRIBUTES = [
        'svg' => ['width', 'height', 'viewBox', 'preserveAspectRatio', 'role', 'aria-roledescription'],
        'g' => [],
        'defs' => [],
        'title' => [],
        'desc' => [],
        'marker' => ['viewBox', 'refX', 'refY', 'markerWidth', 'markerHeight', 'markerUnits', 'orient', 'preserveAspectRatio'],
        'clipPath' => ['clipPathUnits'],
        'path' => ['d'],
        'rect' => ['x', 'y', 'width', 'height', 'rx', 'ry'],
        'circle' => ['cx', 'cy', 'r'],
        'ellipse' => ['cx', 'cy', 'rx', 'ry'],
        'line' => ['x1', 'y1', 'x2', 'y2'],
        'polyline' => ['points'],
        'polygon' => ['points'],
        'text' => ['x', 'y', 'dx', 'dy', 'text-anchor', 'dominant-baseline', 'font-family', 'font-size', 'font-weight', 'font-style'],
        'tspan' => ['x', 'y', 'dx', 'dy', 'text-anchor', 'dominant-baseline', 'font-family', 'font-size', 'font-weight', 'font-style'],
    ];

    private const SHARED_ATTRIBUTES = [
        'transform', 'fill', 'fill-opacity', 'fill-rule', 'stroke', 'stroke-width', 'stroke-opacity',
        'stroke-dasharray', 'stroke-dashoffset', 'stroke-linecap', 'stroke-linejoin', 'stroke-miterlimit',
        'opacity', 'clip-path', 'clip-rule', 'marker-start', 'marker-mid', 'marker-end',
    ];

    private const SHARED_ATTRIBUTE_EXCLUSIONS = ['svg', 'defs', 'title', 'desc'];

    /**
     * Normalize line endings and surrounding whitespace so browser and server hash identical source.
     */
    public static function normalizeSource(?string $source): string
    {
        return trim(str_replace(["\r\n", "\r"], "\n", (string) $source), " \t\n\r\0\x0B");
    }

    /**
     * SHA-256 hex digest of the normalized Mermaid source.
     */
    public static function hashSource(?string $source): string
    {
        return hash('sha256', self::normalizeSource($source));
    }

    public static function hasSource(?string $source): bool
    {
        return self::normalizeSource($source) !== '';
    }

    /**
     * Whether a stored SVG belongs to the given Mermaid source.
     */
    public static function isCurrent(?string $source, ?string $svg, ?string $hash): bool
    {
        if (! self::hasSource($source) || blank($svg) || blank($hash)) {
            return false;
        }

        return hash_equals(self::hashSource($source), (string) $hash);
    }

    /**
     * Assert that an issue's persisted diagram is ready for report output.
     *
     * Returns null when the issue has no Mermaid source, otherwise the re-sanitized SVG.
     *
     * @throws ValidationException when the issue has Mermaid source but no current, safe SVG.
     */
    public static function assertCurrent(AccessibilityIssue $issue, string $errorKey = 'mermaid_diagram'): ?string
    {
        if (! self::hasSource($issue->mermaid_diagram)) {
            return null;
        }

        if (! self::isCurrent($issue->mermaid_diagram, $issue->mermaid_svg, $issue->mermaid_source_hash)) {
            throw ValidationException::withMessages([
                $errorKey => __('The Mermaid diagram for issue ":title" has not been rendered for the current diagram source. Open the issue, edit it and save it again so the diagram is rendered before generating the report.', [
                    'title' => $issue->title,
                ]),
            ]);
        }

        try {
            return (new self)->sanitize((string) $issue->mermaid_svg, $errorKey);
        } catch (ValidationException) {
            throw ValidationException::withMessages([
                $errorKey => __('The stored Mermaid diagram for issue ":title" contains unsupported or unsafe content. Open the issue, edit it and save it again.', [
                    'title' => $issue->title,
                ]),
            ]);
        }
    }

    public static function assertExampleCurrent(WcagCriterionExample $example, string $errorKey = 'report'): string
    {
        if (! self::isCurrent($example->code_snippet, $example->mermaid_svg, $example->mermaid_source_hash)) {
            throw ValidationException::withMessages([
                $errorKey => __('The diagram for example ":title" is not ready for the report. Edit and save the example in the knowledge base to render it.', ['title' => $example->title]),
            ]);
        }

        try {
            return (new self)->sanitize($example->mermaid_svg, $errorKey);
        } catch (ValidationException) {
            throw ValidationException::withMessages([
                $errorKey => __('The diagram for example ":title" could not be used. Edit and save the example in the knowledge base.', ['title' => $example->title]),
            ]);
        }
    }

    /**
     * Validate that the SVG was rendered from the given source and return sanitized persistence fields.
     *
     * @return array{mermaid_svg: ?string, mermaid_source_hash: ?string}
     *
     * @throws ValidationException
     */
    public function validate(string $source, ?string $svg, ?string $hash, string $errorKey = 'mermaid_diagram'): array
    {
        if (! self::hasSource($source)) {
            return ['mermaid_svg' => null, 'mermaid_source_hash' => null];
        }

        if (blank($svg) || blank($hash)) {
            $this->fail($errorKey, __('The Mermaid diagram could not be rendered to SVG. Check the diagram syntax and save again in a browser with JavaScript enabled.'));
        }

        $expectedHash = self::hashSource($source);

        if (! hash_equals($expectedHash, (string) $hash)) {
            $this->fail($errorKey, __('The rendered Mermaid SVG does not match the current diagram source. Save again to render the diagram anew.'));
        }

        return [
            'mermaid_svg' => $this->sanitize((string) $svg, $errorKey),
            'mermaid_source_hash' => $expectedHash,
        ];
    }

    /**
     * Parse and re-serialize an SVG, rejecting anything outside the strict inline-geometry allowlist.
     *
     * @throws ValidationException
     */
    public function sanitize(string $svg, string $errorKey = 'mermaid_diagram'): string
    {
        if (strlen($svg) > self::MAX_SVG_BYTES) {
            $this->fail($errorKey, __('The rendered Mermaid SVG is too large. Simplify the diagram.'));
        }

        if (preg_match('/<!(?:DOCTYPE|ENTITY|ATTLIST|ELEMENT|NOTATION)|<!\[CDATA\[|<\?(?!xml\s)/i', $svg)) {
            $this->unsafe($errorKey);
        }

        $document = new DOMDocument;
        $previous = libxml_use_internal_errors(true);

        try {
            $loaded = $document->loadXML($svg, LIBXML_NONET);
            $errors = libxml_get_errors();
        } finally {
            libxml_clear_errors();
            libxml_use_internal_errors($previous);
        }

        if (! $loaded || $errors !== [] || $document->doctype !== null) {
            $this->fail($errorKey, __('The rendered Mermaid SVG is not valid XML.'));
        }

        $root = $document->documentElement;

        if (! $root instanceof DOMElement || $root->localName !== 'svg') {
            $this->unsafe($errorKey);
        }

        $ids = [];
        $references = [];

        $this->sanitizeElement($root, $ids, $references, $errorKey);

        foreach ($references as $reference) {
            if (! isset($ids[$reference])) {
                $this->unsafe($errorKey);
            }
        }

        $sanitized = $document->saveXML($root);

        if ($sanitized === false) {
            $this->unsafe($errorKey);
        }

        return $sanitized;
    }

    /**
     * @param  array<string, true>  $ids
     * @param  list<string>  $references
     */
    private function sanitizeElement(DOMElement $element, array &$ids, array &$references, string $errorKey): void
    {
        if ($element->namespaceURI !== self::SVG_NAMESPACE
            || ($element->prefix !== null && $element->prefix !== '')
            || ! array_key_exists($element->localName, self::ELEMENT_ATTRIBUTES)) {
            $this->unsafe($errorKey);
        }

        foreach (iterator_to_array($element->attributes) as $attribute) {
            $this->assertSafeAttribute($element->localName, $attribute, $ids, $references, $errorKey);
        }

        foreach (iterator_to_array($element->childNodes) as $child) {
            match ($child->nodeType) {
                XML_ELEMENT_NODE => $this->sanitizeElement($child, $ids, $references, $errorKey),
                XML_TEXT_NODE => $this->sanitizeText($element->localName, $child, $errorKey),
                XML_COMMENT_NODE => $element->removeChild($child),
                default => $this->unsafe($errorKey),
            };
        }
    }

    private function sanitizeText(string $parentName, DOMNode $text, string $errorKey): void
    {
        if (in_array($parentName, self::TEXT_ELEMENTS, true)) {
            return;
        }

        if (trim((string) $text->nodeValue) !== '') {
            $this->unsafe($errorKey);
        }

        $text->parentNode?->removeChild($text);
    }

    /**
     * @param  array<string, true>  $ids
     * @param  list<string>  $references
     */
    private function assertSafeAttribute(string $elementName, DOMAttr $attribute, array &$ids, array &$references, string $errorKey): void
    {
        $name = $attribute->nodeName;
        $value = trim($attribute->value);

        if ($attribute->namespaceURI !== null || ! $this->attributeAllowed($elementName, $name)) {
            $this->unsafe($errorKey);
        }

        if (preg_match('/[<>\\\\@;{}]|javascript:|data:|expression\s*\(|\/\*/i', $value)) {
            $this->unsafe($errorKey);
        }

        if (str_contains(strtolower($value), 'url(')) {
            if (! in_array($name, self::REFERENCE_ATTRIBUTES, true) || ! preg_match(self::LOCAL_REFERENCE_PATTERN, $value, $matches)) {
                $this->unsafe($errorKey);
            }

            $references[] = $matches[1];

            return;
        }

        if ($name === 'id') {
            if (! preg_match(self::ID_PATTERN, $value) || isset($ids[$value])) {
                $this->unsafe($errorKey);
            }

            $ids[$value] = true;

            return;
        }

        if (! preg_match($this->valuePattern($name), $value)) {
            $this->unsafe($errorKey);
        }

        if ($name === 'stroke-dashoffset') {
            if (! preg_match('/^([-+]?(?:\d+\.?\d*|\.\d+)(?:[eE][-+]?\d+)?)(?:px)?$/', $value, $matches)) {
                $this->unsafe($errorKey);
            }

            $attribute->value = $matches[1];
        }
    }

    private function attributeAllowed(string $elementName, string $attributeName): bool
    {
        if ($attributeName === 'id' || in_array($attributeName, self::ELEMENT_ATTRIBUTES[$elementName], true)) {
            return true;
        }

        return ! in_array($elementName, self::SHARED_ATTRIBUTE_EXCLUSIONS, true)
            && in_array($attributeName, self::SHARED_ATTRIBUTES, true);
    }

    private function valuePattern(string $attributeName): string
    {
        $number = '[-+]?(?:\d+\.?\d*|\.\d+)(?:[eE][-+]?\d+)?';
        $length = "{$number}(?:px|%|em|ex|pt)?";

        return match ($attributeName) {
            'x', 'y', 'dx', 'dy', 'width', 'height', 'rx', 'ry', 'cx', 'cy', 'r', 'x1', 'y1', 'x2', 'y2',
            'refX', 'refY', 'markerWidth', 'markerHeight', 'stroke-width', 'stroke-dashoffset', 'font-size' => "/^(?:{$length}(?:[\s,]+{$length})*)?$/",
            'opacity', 'fill-opacity', 'stroke-opacity', 'stroke-miterlimit' => "/^{$number}%?$/",
            'viewBox', 'points' => '/^[0-9eE.,+\-\s]*$/',
            'stroke-dasharray' => "/^(?:none|{$length}(?:[\s,]+{$length})*)$/",
            'd' => '/^[MmZzLlHhVvCcSsQqTtAa0-9eE.,+\-\s]*$/',
            'transform' => '/^(?:\s*(?:translate|scale|rotate|matrix|skewX|skewY)\s*\([0-9eE.,+\-\s]*\)\s*,?)*$/',
            'fill', 'stroke' => '/^(?:none|transparent|currentColor|[a-zA-Z]{3,20}|#[0-9a-fA-F]{3,8}|rgba?\(\s*[0-9.]+%?\s*,\s*[0-9.]+%?\s*,\s*[0-9.]+%?\s*(?:,\s*[0-9.]+%?\s*)?\))$/',
            'fill-rule', 'clip-rule' => '/^(?:nonzero|evenodd|inherit)$/',
            'stroke-linecap' => '/^(?:butt|round|square|inherit)$/',
            'stroke-linejoin' => '/^(?:miter|round|bevel|arcs|miter-clip|inherit)$/',
            'clip-path', 'marker-start', 'marker-mid', 'marker-end' => '/^none$/',
            'markerUnits' => '/^(?:strokeWidth|userSpaceOnUse)$/',
            'clipPathUnits' => '/^(?:userSpaceOnUse|objectBoundingBox)$/',
            'orient' => "/^(?:auto|auto-start-reverse|{$number}(?:deg)?)$/",
            'preserveAspectRatio' => '/^(?:none|x(?:Min|Mid|Max)Y(?:Min|Mid|Max))(?:\s+(?:meet|slice))?$/',
            'text-anchor' => '/^(?:start|middle|end)$/',
            'dominant-baseline' => '/^(?:auto|middle|central|hanging|alphabetic|ideographic|mathematical|text-top|text-bottom|text-after-edge|text-before-edge)$/',
            'font-family' => '/^[A-Za-z0-9\s,"\'\-]{1,200}$/',
            'font-weight' => '/^(?:normal|bold|bolder|lighter|[1-9]00)$/',
            'font-style' => '/^(?:normal|italic|oblique)$/',
            'role', 'aria-roledescription' => '/^[A-Za-z0-9\s\-_]{1,100}$/',
            default => '/(?!)/',
        };
    }

    /**
     * @throws ValidationException
     */
    private function unsafe(string $errorKey): never
    {
        $this->fail($errorKey, __('The rendered Mermaid SVG contains unsupported or unsafe content. Only plain diagram shapes and text can be saved.'));
    }

    /**
     * @throws ValidationException
     */
    private function fail(string $errorKey, string $message): never
    {
        throw ValidationException::withMessages([$errorKey => $message]);
    }
}
