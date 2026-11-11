<?php declare(strict_types=1);

namespace Banner\Stdlib;

use HTMLPurifier;
use HTMLPurifier_Config;

/**
 * Sanitizes the HTML that the banner editor stores.
 *
 * Banner text is rendered with innerHTML so the editor's bold, italic,
 * underline, and link markup is visible. That makes server-side sanitation
 * mandatory: only the tags and attributes behind those four features survive.
 * Inline styles, classes, scripts, event handlers, and unknown elements are
 * removed. Links are limited to http, https, mailto, and relative URLs.
 */
class HtmlSanitizer
{
    /**
     * Allowed elements and attributes (HTMLPurifier "HTML.Allowed" syntax).
     */
    public const ALLOWED_HTML = 'p,br,strong,b,em,i,u,a[href|title|target],ul,ol,li';

    private HTMLPurifier $purifier;

    public function __construct()
    {
        $config = HTMLPurifier_Config::createDefault();
        $config->set('HTML.Allowed', self::ALLOWED_HTML);
        $config->set('Attr.AllowedFrameTargets', ['_blank']);
        $config->set('AutoFormat.RemoveEmpty', true);
        $config->set('AutoFormat.RemoveEmpty.RemoveNbsp', true);
        $config->set('URI.AllowedSchemes', [
            'http' => true,
            'https' => true,
            'mailto' => true,
        ]);
        // Avoid depending on a writable cache directory.
        $config->set('Cache.DefinitionImpl', null);
        $this->purifier = new HTMLPurifier($config);
    }

    /**
     * Return the sanitized HTML for banner text.
     */
    public function sanitize(string $html): string
    {
        return trim($this->purifier->purify($html));
    }

    /**
     * Prepare a stored banner value for display.
     *
     * Banner text saved before this module supported formatting is plain text,
     * not HTML. When a stored value contains no tag-like markup, escape it as
     * text so angle brackets and line breaks stay intact instead of being
     * treated as markup. Values that do contain markup are sanitized.
     */
    public function toDisplayHtml(string $stored): string
    {
        if (!preg_match('/<\/?[a-z][^>]*>/i', $stored)) {
            // Preserve plain-text line breaks as <br>; block markup from the
            // editor already carries its own line structure.
            return str_replace(
                ["\r\n", "\r", "\n"],
                '<br>',
                htmlspecialchars($stored, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8')
            );
        }

        return $this->sanitize($stored);
    }
}
