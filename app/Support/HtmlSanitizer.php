<?php

namespace App\Support;

use DOMDocument;
use DOMElement;
use DOMNode;

/**
 * Allow-list HTML sanitizer for rich article bodies.
 *
 * Keeps ordinary article formatting (headings, paragraphs, lists, links,
 * images, tables, quotes, YouTube/Vimeo/Facebook embeds) and removes
 * everything that can run script: <script>, event handlers (onclick=...),
 * javascript:/data: URLs, <style>, <svg>, <object>, <form>, unknown iframes...
 *
 * Dependency-free (uses PHP's built-in DOM extension) so it works on a stock
 * Laravel install. Used both when an article is saved (Article model) and when
 * it is rendered (article-show view), so legacy rows are protected too.
 */
class HtmlSanitizer
{
    /** tag => allowed attributes */
    private const ALLOWED = [
        'p' => ['class'], 'br' => [], 'hr' => [],
        'h1' => ['class'], 'h2' => ['class'], 'h3' => ['class'],
        'h4' => ['class'], 'h5' => ['class'], 'h6' => ['class'],
        'strong' => [], 'b' => [], 'em' => [], 'i' => [], 'u' => [], 's' => [],
        'sub' => [], 'sup' => [], 'small' => [], 'mark' => [],
        'blockquote' => ['class', 'cite'], 'pre' => ['class'], 'code' => ['class'],
        'ul' => ['class'], 'ol' => ['class', 'start'], 'li' => ['class'],
        'span' => ['class'], 'div' => ['class'],
        'figure' => ['class'], 'figcaption' => ['class'],
        'a' => ['href', 'title', 'target', 'rel', 'class'],
        'img' => ['src', 'alt', 'title', 'width', 'height', 'class', 'loading'],
        'table' => ['class'], 'thead' => [], 'tbody' => [], 'tfoot' => [],
        'tr' => [], 'th' => ['colspan', 'rowspan', 'scope'], 'td' => ['colspan', 'rowspan'],
        'caption' => [],
        'iframe' => ['src', 'width', 'height', 'title', 'allowfullscreen', 'frameborder', 'allow', 'class'],
    ];

    /** Elements removed together with everything inside them. */
    private const DROP_WITH_CONTENT = [
        'script', 'style', 'template', 'noscript', 'object', 'embed', 'applet',
        'svg', 'math', 'form', 'textarea', 'select', 'button', 'head', 'title',
        'link', 'meta', 'base', 'frame', 'frameset', 'audio', 'video', 'canvas',
    ];

    private const URL_ATTRS = ['href', 'src', 'cite'];

    /** Hosts allowed in <iframe src>. */
    private const IFRAME_HOSTS = [
        'www.youtube.com', 'youtube.com', 'www.youtube-nocookie.com',
        'player.vimeo.com', 'www.facebook.com',
    ];

    public static function clean(?string $html): string
    {
        $html = (string) $html;
        if (trim($html) === '') {
            return '';
        }

        $previous = libxml_use_internal_errors(true);

        $doc = new DOMDocument('1.0', 'UTF-8');
        // The <?xml encoding hint makes libxml treat input as UTF-8 (Tamil/Sinhala safe).
        $doc->loadHTML('<?xml encoding="UTF-8"><html><body><div id="__root">'.$html.'</div></body></html>',
            LIBXML_NONET | LIBXML_NOERROR | LIBXML_NOWARNING);

        libxml_clear_errors();
        libxml_use_internal_errors($previous);

        $root = $doc->getElementById('__root');
        if (! $root) {
            return htmlspecialchars(strip_tags($html), ENT_QUOTES, 'UTF-8');
        }

        self::sanitizeChildren($root);

        $out = '';
        foreach ($root->childNodes as $child) {
            $out .= $doc->saveHTML($child);
        }

        return $out;
    }

    private static function sanitizeChildren(DOMNode $parent): void
    {
        // Snapshot: the live NodeList changes while we edit.
        foreach (iterator_to_array($parent->childNodes) as $node) {
            if ($node instanceof DOMElement) {
                self::sanitizeElement($node);
            } elseif ($node->nodeType !== XML_TEXT_NODE) {
                // comments, processing instructions, CDATA...
                $parent->removeChild($node);
            }
        }
    }

    private static function sanitizeElement(DOMElement $el): void
    {
        $tag = strtolower($el->tagName);

        if (in_array($tag, self::DROP_WITH_CONTENT, true)) {
            $el->parentNode->removeChild($el);

            return;
        }

        if (! isset(self::ALLOWED[$tag])) {
            // Unknown/unsafe wrapper: keep its (sanitized) children, drop the tag.
            self::sanitizeChildren($el);
            while ($el->firstChild) {
                $el->parentNode->insertBefore($el->firstChild, $el);
            }
            $el->parentNode->removeChild($el);

            return;
        }

        $allowedAttrs = self::ALLOWED[$tag];
        foreach (iterator_to_array($el->attributes) as $attr) {
            $name = strtolower($attr->nodeName);
            if (! in_array($name, $allowedAttrs, true)) {
                $el->removeAttribute($attr->nodeName);
                continue;
            }
            if (in_array($name, self::URL_ATTRS, true) && ! self::isSafeUrl($attr->nodeValue, $tag === 'iframe')) {
                $el->removeAttribute($attr->nodeName);
            }
        }

        if ($tag === 'iframe') {
            $src = $el->getAttribute('src');
            if ($src === '' || ! self::isAllowedIframe($src)) {
                $el->parentNode->removeChild($el);

                return;
            }
            $el->setAttribute('loading', 'lazy');
            $el->setAttribute('referrerpolicy', 'strict-origin-when-cross-origin');
        }

        if ($tag === 'a') {
            if ($el->hasAttribute('target') && ! in_array(strtolower($el->getAttribute('target')), ['_blank', '_self'], true)) {
                $el->removeAttribute('target');
            }
            if ($el->getAttribute('target') === '_blank') {
                $el->setAttribute('rel', 'noopener noreferrer');
            }
        }

        if ($tag === 'img' && ! $el->hasAttribute('src')) {
            $el->parentNode->removeChild($el);

            return;
        }

        self::sanitizeChildren($el);
    }

    /**
     * Only http(s), mailto, tel, and scheme-less (relative / protocol-relative / #anchor) URLs.
     */
    private static function isSafeUrl(?string $url, bool $httpsOnly = false): bool
    {
        // Browsers ignore control chars and whitespace inside the scheme ("java\tscript:").
        $normalized = preg_replace('/[\x00-\x20\x7f-\x9f\s]+/u', '', (string) $url);
        if ($normalized === null || $normalized === '') {
            return false;
        }

        if (preg_match('/^([a-z][a-z0-9+.\-]*):/i', $normalized, $m)) {
            $allowed = $httpsOnly ? ['http', 'https'] : ['http', 'https', 'mailto', 'tel'];

            return in_array(strtolower($m[1]), $allowed, true);
        }

        // No scheme. Reject anything with a stray colon before the first slash
        // (odd encodings) just to be conservative.
        $beforeSlash = strstr($normalized, '/', true);
        if (str_contains($beforeSlash === false ? $normalized : $beforeSlash, ':')) {
            return false;
        }

        return true;
    }

    private static function isAllowedIframe(string $src): bool
    {
        $src = trim($src);
        if (str_starts_with($src, '//')) {
            $src = 'https:'.$src;
        }

        $parts = parse_url($src);
        if (! $parts || ! isset($parts['scheme'], $parts['host'])) {
            return false;
        }

        return in_array(strtolower($parts['scheme']), ['http', 'https'], true)
            && in_array(strtolower($parts['host']), self::IFRAME_HOSTS, true);
    }
}
