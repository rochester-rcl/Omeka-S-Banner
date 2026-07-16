<?php declare(strict_types=1);

namespace BannerTest;

use Banner\Module;
use PHPUnit\Framework\TestCase;

/**
 * Verifies that Banner\Module::getContentSelector returns the correct DOM
 * element ID for every installed theme.
 *
 * Each theme's main content area may use a different HTML id attribute.
 * The module must target the right element so the banner is inserted
 * adjacent to (not inside) the content wrapper.
 *
 * Theme layout files examined:
 *   bookshelf     → themes/bookshelf/bookshelf/view/layout/layout.phtml
 *   centerrow     → themes/centerrow/view/layout/layout.phtml
 *   cozy          → themes/cozy/cozy/view/layout/layout.phtml
 *   default       → themes/default/view/layout/layout.phtml
 *   foundation    → themes/foundation/foundation/view/layout/layout.phtml
 *   freedom       → themes/freedom/freedom/view/layout/layout.phtml   [uses #main-content]
 *   freedom-v1.1.0→ themes/freedom-v1.1.0/freedom/view/layout/layout.phtml
 *   lively        → themes/lively/lively/view/layout/layout.phtml
 *   papers        → themes/papers/papers/view/layout/layout.phtml
 *   thanksroy     → themes/thanksroy/thanksroy/view/layout/layout.phtml
 *   thedaily      → themes/thedaily/thedaily/view/layout/layout.phtml
 */
class BannerThemeCompatibilityTest extends TestCase
{
    private Module $module;

    protected function setUp(): void
    {
        $this->module = new Module();
    }

    public function testBookshelfThemeContentSelector(): void
    {
        $this->assertSame(
            'content',
            $this->module->getContentSelector('bookshelf'),
            'bookshelf theme main content element must have id="content"'
        );
    }

    public function testCenterrowThemeContentSelector(): void
    {
        $this->assertSame(
            'content',
            $this->module->getContentSelector('centerrow'),
            'centerrow theme main content element must have id="content"'
        );
    }

    public function testCozyThemeContentSelector(): void
    {
        $this->assertSame(
            'content',
            $this->module->getContentSelector('cozy'),
            'cozy theme main content element must have id="content"'
        );
    }

    public function testDefaultThemeContentSelector(): void
    {
        $this->assertSame(
            'content',
            $this->module->getContentSelector('default'),
            'default theme main content element must have id="content"'
        );
    }

    public function testFoundationThemeContentSelector(): void
    {
        $this->assertSame(
            'content',
            $this->module->getContentSelector('foundation'),
            'foundation theme main content element must have id="content"'
        );
    }

    public function testFreedomThemeContentSelector(): void
    {
        $this->assertSame(
            'main-content',
            $this->module->getContentSelector('freedom'),
            'freedom theme uses id="main-content" instead of id="content"; banner JS must target #main-content'
        );
    }

    public function testFreedomV110ThemeContentSelector(): void
    {
        $this->assertSame(
            'content',
            $this->module->getContentSelector('freedom-v1.1.0'),
            'freedom-v1.1.0 theme main content element must have id="content"'
        );
    }

    public function testLivelyThemeContentSelector(): void
    {
        $this->assertSame(
            'content',
            $this->module->getContentSelector('lively'),
            'lively theme main content element must have id="content"'
        );
    }

    public function testPapersThemeContentSelector(): void
    {
        $this->assertSame(
            'content',
            $this->module->getContentSelector('papers'),
            'papers theme main content element must have id="content"'
        );
    }

    public function testThanksroyThemeContentSelector(): void
    {
        $this->assertSame(
            'content',
            $this->module->getContentSelector('thanksroy'),
            'thanksroy theme main content element must have id="content"'
        );
    }

    public function testThedailyThemeContentSelector(): void
    {
        $this->assertSame(
            'content',
            $this->module->getContentSelector('thedaily'),
            'thedaily theme main content element must have id="content"'
        );
    }

    public function testUnknownThemeFallsBackToContent(): void
    {
        $this->assertSame(
            'content',
            $this->module->getContentSelector('some-future-theme'),
            'unknown themes must fall back to id="content"'
        );
    }
}
