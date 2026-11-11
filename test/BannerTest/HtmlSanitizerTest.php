<?php declare(strict_types=1);

namespace BannerTest;

use Banner\Stdlib\HtmlSanitizer;
use PHPUnit\Framework\TestCase;

/**
 * The banner stores HTML, so only the markup needed for the four supported
 * formatting features may survive.
 */
class HtmlSanitizerTest extends TestCase
{
    private HtmlSanitizer $sanitizer;

    protected function setUp(): void
    {
        $this->sanitizer = new HtmlSanitizer();
    }

    public function testKeepsSupportedFormatting(): void
    {
        $html = '<p><strong>bold</strong> <b>bold too</b> <em>italic</em> '
            . '<i>italic too</i> <u>underlined</u></p>';

        $this->assertSame($html, $this->sanitizer->sanitize($html));
    }

    public function testKeepsSafeLinks(): void
    {
        $html = '<p><a href="https://example.org/path?q=1&amp;r=2">link</a></p>';

        $this->assertStringContainsString('<a href="https://example.org/path?q=1&amp;r=2">link</a>', $this->sanitizer->sanitize($html));
    }

    public function testKeepsRelativeAndMailtoLinks(): void
    {
        $relative = $this->sanitizer->sanitize('<a href="/about">about</a>');
        $mailto = $this->sanitizer->sanitize('<a href="mailto:help@example.org">email</a>');

        $this->assertStringContainsString('href="/about"', $relative);
        $this->assertStringContainsString('href="mailto:help@example.org"', $mailto);
    }

    public function testStripsScriptsAndEventHandlers(): void
    {
        $html = '<p onclick="alert(1)">text</p><script>alert(2)</script>';

        $clean = $this->sanitizer->sanitize($html);

        $this->assertStringNotContainsString('script', $clean);
        $this->assertStringNotContainsString('onclick', $clean);
    }

    public function testStripsStylesClassesAndDisallowedElements(): void
    {
        $html = '<p style="color:red" class="x">styled</p><iframe src="https://example.org"></iframe><img src="x">';

        $clean = $this->sanitizer->sanitize($html);

        $this->assertSame('<p>styled</p>', $clean);
        $this->assertStringNotContainsString('iframe', $clean);
        $this->assertStringNotContainsString('<img', $clean);
    }

    public function testStripsJavascriptUrls(): void
    {
        $clean = $this->sanitizer->sanitize('<a href="javascript:alert(1)">bad</a>');

        $this->assertStringNotContainsString('javascript:', $clean);
    }

    public function testPlainTextIsPreservedAndEscaped(): void
    {
        $clean = $this->sanitizer->toDisplayHtml("Line one\nLine < two >");

        $this->assertSame('Line one<br>Line &lt; two &gt;', $clean);
    }

    public function testEmptyEditorParagraphsAreRemoved(): void
    {
        $clean = $this->sanitizer->sanitize('<p>one</p><p>&nbsp;</p><p>two</p>');

        $this->assertSame('<p>one</p><p>two</p>', $clean);
    }

    public function testLegacyPlainTextThatLooksLikeMarkupIsSanitized(): void
    {
        $clean = $this->sanitizer->toDisplayHtml('<p onclick="alert(1)">text</p><script>alert(2)</script>');

        $this->assertSame('<p>text</p>', $clean);
    }

    public function testDisplayHtmlKeepsSupportedFormatting(): void
    {
        $clean = $this->sanitizer->toDisplayHtml('<p><strong>bold</strong> <a href="https://example.org">link</a></p>');

        $this->assertSame('<p><strong>bold</strong> <a href="https://example.org">link</a></p>', $clean);
    }

    public function testListsAreKept(): void
    {
        $clean = $this->sanitizer->sanitize('<ul><li>one</li><li>two</li></ul>');

        $this->assertStringContainsString('<ul><li>one</li><li>two</li></ul>', $clean);
    }

    public function testEmptyInputReturnsEmptyString(): void
    {
        $this->assertSame('', $this->sanitizer->sanitize(''));
    }
}
