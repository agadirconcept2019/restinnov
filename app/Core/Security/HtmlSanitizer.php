<?php

namespace App\Core\Security;

use DOMDocument;
use DOMElement;

class HtmlSanitizer
{
    private array $allowedTags = ['p', 'h1', 'h2', 'h3', 'h4', 'h5', 'h6', 'ul', 'ol', 'li', 'a', 'strong', 'em', 'img', 'blockquote', 'br'];
    private array $allowedAttributes = ['a' => ['href', 'target', 'rel'], 'img' => ['src', 'alt']];

    public function sanitize(?string $html): ?string
    {
        if (! $html) {
            return $html;
        }

        $doc = new DOMDocument('1.0', 'UTF-8');
        libxml_use_internal_errors(true);
        $doc->loadHTML('<?xml encoding="utf-8" ?><body>'.$html.'</body>', LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
        libxml_clear_errors();

        $body = $doc->getElementsByTagName('body')->item(0);
        if (! $body) {
            return strip_tags($html);
        }

        $this->sanitizeNode($body);

        $output = '';
        foreach ($body->childNodes as $node) {
            $output .= $doc->saveHTML($node);
        }

        return $output;
    }

    private function sanitizeNode(\DOMNode $node): void
    {
        if ($node instanceof DOMElement) {
            $tag = strtolower($node->tagName);
            if (! in_array($tag, $this->allowedTags, true) && $tag !== 'body') {
                $node->parentNode?->removeChild($node);

                return;
            }

            if ($node->hasAttributes()) {
                foreach (iterator_to_array($node->attributes) as $attr) {
                    $name = strtolower($attr->name);
                    $allowed = $this->allowedAttributes[$tag] ?? [];
                    if (str_starts_with($name, 'on') || ! in_array($name, $allowed, true)) {
                        $node->removeAttribute($name);
                        continue;
                    }

                    if ($tag === 'a' && $name === 'href' && preg_match('/^\s*javascript:/i', $attr->value)) {
                        $node->removeAttribute('href');
                        continue;
                    }

                    if ($tag === 'a' && $name === 'target' && $attr->value === '_blank') {
                        $node->setAttribute('rel', 'noopener noreferrer');
                    }
                }
            }
        }

        foreach (iterator_to_array($node->childNodes) as $child) {
            $this->sanitizeNode($child);
        }
    }
}
