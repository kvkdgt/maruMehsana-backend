<?php

namespace App\Support;

use DOMDocument;
use DOMElement;
use DOMNode;

/**
 * Small allow-list HTML sanitizer for article bodies shown on the public website.
 *
 * Keeps basic text formatting, lists, sub-headings, quotes, links and images.
 * Drops script/style/iframe/forms etc. together with their contents, unwraps every
 * other unknown tag (keeping its text), removes all attributes except a[href] and
 * img[src|alt], and rejects javascript:/data:/vbscript: and other non-http(s) URLs.
 */
class HtmlSanitizer
{
    /** Tags kept as they are (with no attributes unless listed in ALLOWED_ATTRS). */
    private const ALLOWED_TAGS = [
        'p', 'br', 'strong', 'b', 'em', 'i', 'u', 'ul', 'ol', 'li',
        'h2', 'h3', 'h4', 'blockquote', 'a', 'img',
    ];

    /** Tags renamed to an allowed one (the page already has an h1). */
    private const RENAME_TAGS = ['h1' => 'h2', 'h5' => 'h4', 'h6' => 'h4'];

    /** Tags removed together with everything inside them. */
    private const DROP_TAGS = [
        'script', 'style', 'iframe', 'frame', 'frameset', 'object', 'embed', 'applet',
        'noscript', 'template', 'form', 'input', 'button', 'select', 'option', 'textarea',
        'svg', 'math', 'head', 'title', 'meta', 'link', 'base', 'video', 'audio', 'source',
        'canvas', 'dialog',
    ];

    /**
     * True when the text contains known HTML tags. Plain text with other "<...>" bits (e.g. "<more>")
     * stays plain text and is escaped, so it is shown as typed instead of being swallowed as a tag.
     */
    public static function isHtml(?string $text): bool
    {
        return (bool) preg_match(
            '~</?(?:p|br|div|span|b|strong|i|em|u|ul|ol|li|h[1-6]|a|img|blockquote|table|tr|td|th|tbody|thead|figure|figcaption|section|article|font|center|hr|pre|code|small|sup|sub|script|style|iframe|object|embed|svg|form|input|button|link|meta)\b[^>]*>~i',
            (string) $text
        );
    }

    /**
     * Article body as safe HTML: sanitized when it contains tags, otherwise escaped plain
     * text with line breaks kept.
     */
    public static function render(?string $text): string
    {
        $text = trim((string) $text);
        if ($text === '') {
            return '';
        }

        if (!self::isHtml($text)) {
            $paragraphs = preg_split('/\R{2,}/u', $text) ?: [$text];

            return implode('', array_map(fn ($p) => '<p>' . nl2br(e(trim($p)), false) . '</p>', $paragraphs));
        }

        return self::clean($text);
    }

    public static function clean(string $html): string
    {
        $doc = new DOMDocument('1.0', 'UTF-8');
        $previous = libxml_use_internal_errors(true);
        $doc->loadHTML(
            '<!DOCTYPE html><html><head><meta http-equiv="Content-Type" content="text/html; charset=utf-8"></head><body><div id="__nwd_root">'
            . $html . '</div></body></html>',
            LIBXML_NONET | LIBXML_HTML_NODEFDTD
        );
        libxml_clear_errors();
        libxml_use_internal_errors($previous);

        $root = $doc->getElementById('__nwd_root');
        if (!$root) {
            return '';
        }

        self::cleanChildren($root);

        $out = '';
        foreach ($root->childNodes as $child) {
            $out .= $doc->saveHTML($child);
        }

        return trim($out);
    }

    private static function cleanChildren(DOMNode $parent): void
    {
        // Copy first: the live list changes while we remove/unwrap nodes.
        foreach (iterator_to_array($parent->childNodes) as $node) {
            self::cleanNode($node);
        }
    }

    private static function cleanNode(DOMNode $node): void
    {
        if ($node->nodeType === XML_TEXT_NODE) {
            return;
        }

        if (!$node instanceof DOMElement) {
            // Comments, CDATA, processing instructions...
            $node->parentNode?->removeChild($node);

            return;
        }

        $tag = strtolower($node->tagName);

        if (in_array($tag, self::DROP_TAGS, true)) {
            $node->parentNode->removeChild($node);

            return;
        }

        if (isset(self::RENAME_TAGS[$tag])) {
            $node = self::rename($node, self::RENAME_TAGS[$tag]);
            $tag = self::RENAME_TAGS[$tag];
        }

        if (!in_array($tag, self::ALLOWED_TAGS, true)) {
            // Unknown wrapper (div, span, font, table...): keep its content, drop the tag.
            $parent = $node->parentNode;
            $children = iterator_to_array($node->childNodes);
            foreach ($children as $child) {
                $parent->insertBefore($child, $node);
            }
            $parent->removeChild($node);
            foreach ($children as $child) {
                self::cleanNode($child);
            }

            return;
        }

        self::cleanAttributes($node, $tag);

        if ($tag === 'img' && !$node->hasAttribute('src')) {
            $node->parentNode->removeChild($node);

            return;
        }

        self::cleanChildren($node);
    }

    private static function cleanAttributes(DOMElement $node, string $tag): void
    {
        $href = $tag === 'a' ? self::safeUrl($node->getAttribute('href'), ['http', 'https', 'mailto', 'tel']) : null;
        $src = $tag === 'img' ? self::safeUrl($node->getAttribute('src'), ['http', 'https']) : null;
        $alt = $tag === 'img' ? trim($node->getAttribute('alt')) : '';

        foreach (iterator_to_array($node->attributes) as $attr) {
            $node->removeAttribute($attr->nodeName);
        }

        if ($tag === 'a' && $href !== null) {
            $node->setAttribute('href', $href);
            $node->setAttribute('target', '_blank');
            $node->setAttribute('rel', 'nofollow noopener noreferrer');
        }

        if ($tag === 'img' && $src !== null) {
            $node->setAttribute('src', $src);
            $node->setAttribute('alt', $alt);
            $node->setAttribute('loading', 'lazy');
            $node->setAttribute('decoding', 'async');
        }
    }

    /** The URL when its scheme is allowed (relative URLs are fine), otherwise null. */
    private static function safeUrl(string $url, array $schemes): ?string
    {
        // Browsers ignore control characters and whitespace inside schemes ("java\tscript:").
        $url = trim($url);
        $probe = preg_replace('/[\x00-\x20\x7F]+/', '', html_entity_decode($url, ENT_QUOTES | ENT_HTML5, 'UTF-8'));
        if ($url === '' || $probe === '') {
            return null;
        }
        if (preg_match('/^([a-z][a-z0-9+.\-]*):/i', $probe, $m)) {
            return in_array(strtolower($m[1]), $schemes, true) ? $url : null;
        }
        // No scheme: relative path or protocol-relative "//host"; reject anything odd like "\\host".
        return str_starts_with($probe, '\\') ? null : $url;
    }

    private static function rename(DOMElement $node, string $tag): DOMElement
    {
        $new = $node->ownerDocument->createElement($tag);
        foreach (iterator_to_array($node->childNodes) as $child) {
            $new->appendChild($child);
        }
        $node->parentNode->replaceChild($new, $node);

        return $new;
    }
}
