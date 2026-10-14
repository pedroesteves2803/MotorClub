<?php

namespace App\Services;

use DOMDocument;
use DOMElement;
use DOMNode;

class LegalContentSanitizer
{
    /** @var array<string, list<string>> */
    private const ALLOWED_ATTRIBUTES = [
        'a' => ['href', 'title'],
    ];

    /** @var list<string> */
    private const ALLOWED_TAGS = [
        'a', 'blockquote', 'br', 'em', 'h2', 'h3', 'h4', 'li', 'ol', 'p', 'strong', 'ul',
    ];

    public function sanitize(string $html): string
    {
        $document = new DOMDocument('1.0', 'UTF-8');
        $previous = libxml_use_internal_errors(true);

        try {
            $document->loadHTML(
                '<?xml encoding="UTF-8"><div id="legal-content">'.$html.'</div>',
                LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD | LIBXML_NONET,
            );
        } finally {
            libxml_clear_errors();
            libxml_use_internal_errors($previous);
        }

        $root = $document->getElementById('legal-content');
        if (! $root instanceof DOMElement) {
            return '';
        }

        $this->sanitizeChildren($root);

        $content = '';
        foreach ($root->childNodes as $child) {
            $content .= $document->saveHTML($child);
        }

        return $content;
    }

    private function sanitizeChildren(DOMNode $parent): void
    {
        foreach (iterator_to_array($parent->childNodes) as $child) {
            if (! $child instanceof DOMElement) {
                continue;
            }

            $tag = strtolower($child->tagName);
            if (! in_array($tag, self::ALLOWED_TAGS, true)) {
                if (in_array($tag, ['script', 'style', 'iframe', 'object', 'embed', 'svg', 'math', 'template'], true)) {
                    $parent->removeChild($child);

                    continue;
                }

                while ($child->firstChild) {
                    $parent->insertBefore($child->firstChild, $child);
                }
                $parent->removeChild($child);

                continue;
            }

            $allowedAttributes = self::ALLOWED_ATTRIBUTES[$tag] ?? [];
            foreach (iterator_to_array($child->attributes) as $attribute) {
                $name = strtolower($attribute->name);
                if (! in_array($name, $allowedAttributes, true)) {
                    $child->removeAttributeNode($attribute);
                }
            }

            if ($tag === 'a' && $child->hasAttribute('href') && ! $this->isSafeHref($child->getAttribute('href'))) {
                $child->removeAttribute('href');
            }

            $this->sanitizeChildren($child);
        }
    }

    private function isSafeHref(string $href): bool
    {
        $href = trim($href);
        if ($href === '' || str_starts_with($href, '/')) {
            return true;
        }

        $scheme = strtolower((string) parse_url($href, PHP_URL_SCHEME));

        return in_array($scheme, ['http', 'https', 'mailto'], true);
    }
}
