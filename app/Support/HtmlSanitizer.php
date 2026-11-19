<?php

namespace App\Support;

use DOMDocument;
use DOMElement;
use DOMNode;

/**
 * Small allow-list sanitiser for scraped job ad HTML.
 *
 * Descriptions come from third-party sites/APIs and used to be echoed with
 * {!! !!}, so a hostile ad could run script in a user's session. Only a
 * handful of formatting tags survive; every attribute is dropped except a
 * safe http(s)/mailto href on links.
 */
class HtmlSanitizer
{
    /** Tags that are kept. Anything else is unwrapped (children kept, tag removed). */
    private const ALLOWED = [
        'p', 'br', 'ul', 'ol', 'li', 'strong', 'b', 'em', 'i', 'u',
        'h1', 'h2', 'h3', 'h4', 'h5', 'h6', 'a', 'blockquote', 'span', 'div',
        'table', 'thead', 'tbody', 'tr', 'th', 'td',
    ];

    /** Tags removed together with everything inside them. */
    private const DROPPED = [
        'script', 'style', 'noscript', 'iframe', 'object', 'embed', 'svg', 'math',
        'form', 'input', 'button', 'textarea', 'select', 'option', 'link', 'meta',
        'template', 'head', 'title', 'nav', 'header', 'footer', 'aside', 'img', 'video', 'audio',
    ];

    public static function clean(?string $html): string
    {
        if ($html === null || trim($html) === '') {
            return '';
        }

        $previous = libxml_use_internal_errors(true);

        $dom = new DOMDocument('1.0', 'UTF-8');
        $dom->loadHTML(
            '<?xml encoding="UTF-8"><body><div id="sanitize-root">'.$html.'</div></body>',
            LIBXML_NOERROR | LIBXML_NOWARNING | LIBXML_NONET
        );

        libxml_clear_errors();
        libxml_use_internal_errors($previous);

        $root = $dom->getElementById('sanitize-root');

        if (! $root) {
            return e(strip_tags($html));
        }

        self::sanitizeChildren($root);

        $out = '';

        foreach ($root->childNodes as $child) {
            $out .= $dom->saveHTML($child);
        }

        return trim($out);
    }

    private static function sanitizeChildren(DOMNode $parent): void
    {
        // Copy first: the live NodeList changes while we remove/unwrap nodes.
        foreach (iterator_to_array($parent->childNodes) as $node) {
            if (! $node instanceof DOMElement) {
                // Text nodes are fine; comments and processing instructions go.
                if ($node->nodeType !== XML_TEXT_NODE && $node->nodeType !== XML_CDATA_SECTION_NODE) {
                    $parent->removeChild($node);
                }

                continue;
            }

            $tag = strtolower($node->tagName);

            if (in_array($tag, self::DROPPED, true)) {
                $parent->removeChild($node);

                continue;
            }

            self::sanitizeChildren($node);

            if (! in_array($tag, self::ALLOWED, true)) {
                while ($node->firstChild) {
                    $parent->insertBefore($node->firstChild, $node);
                }

                $parent->removeChild($node);

                continue;
            }

            self::sanitizeAttributes($node, $tag);
        }
    }

    private static function sanitizeAttributes(DOMElement $element, string $tag): void
    {
        $href = $tag === 'a' ? trim($element->getAttribute('href')) : '';

        foreach (iterator_to_array($element->attributes) as $attribute) {
            $element->removeAttributeNode($attribute);
        }

        if ($tag === 'a' && preg_match('#^(https?://|mailto:)#i', $href)) {
            $element->setAttribute('href', $href);
            $element->setAttribute('target', '_blank');
            $element->setAttribute('rel', 'noopener noreferrer nofollow');
        }
    }
}
