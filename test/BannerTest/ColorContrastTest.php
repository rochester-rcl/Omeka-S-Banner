<?php declare(strict_types=1);

namespace BannerTest;

use Banner\Stdlib\ColorContrast;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

/**
 * WCAG 2.1 relative-luminance contrast calculations.
 *
 * @see https://www.w3.org/TR/WCAG21/#dfn-contrast-ratio
 */
class ColorContrastTest extends TestCase
{
    public function testBlackOnWhiteHasMaximumContrast(): void
    {
        $this->assertSame(21.0, ColorContrast::ratio('#000000', '#ffffff'));
    }

    public function testContrastIsSymmetric(): void
    {
        $this->assertSame(
            ColorContrast::ratio('#000000', '#ffffff'),
            ColorContrast::ratio('#ffffff', '#000000')
        );
    }

    public function testShortHexColorsAreSupported(): void
    {
        $this->assertSame(
            ColorContrast::ratio('#000', '#fff'),
            ColorContrast::ratio('#000000', '#ffffff')
        );
    }

    public function testKnownContrastRatioForMidGrey(): void
    {
        // #777777 against white is a well-known WCAG 4.48:1 example.
        $this->assertEqualsWithDelta(4.48, ColorContrast::ratio('#777777', '#ffffff'), 0.01);
    }

    public function testGreenDefaultAgainstWhiteFailsAa(): void
    {
        $this->assertFalse(ColorContrast::meetsAa('#28a745', '#ffffff'));
    }

    public function testDarkGreenAgainstWhiteMeetsAa(): void
    {
        $this->assertTrue(ColorContrast::meetsAa('#1e7e34', '#ffffff'));
    }

    public function testMidGreyAgainstWhiteFailsAaButBlackPasses(): void
    {
        $this->assertFalse(ColorContrast::meetsAa('#777777', '#ffffff'));
        $this->assertTrue(ColorContrast::meetsAa('#777777', '#000000'));
    }

    public function testInvalidColorsAreRejected(): void
    {
        $this->assertFalse(ColorContrast::isValidColor('red'));
        $this->assertFalse(ColorContrast::isValidColor('#abcd'));
        $this->assertFalse(ColorContrast::isValidColor('#gggggg'));
        $this->assertTrue(ColorContrast::isValidColor('#ABCDEF'));
        $this->assertTrue(ColorContrast::isValidColor('#abc'));
    }

    public function testRatioRejectsInvalidColors(): void
    {
        $this->expectException(InvalidArgumentException::class);
        ColorContrast::ratio('red', '#ffffff');
    }

    public function testWarningMessageIsNullWhenAaPasses(): void
    {
        $this->assertNull(ColorContrast::warningMessage('#1e7e34', '#ffffff'));
    }

    public function testWarningMessageExplainsAFailingPairButDoesNotChangeIt(): void
    {
        $message = ColorContrast::warningMessage('#28a745', '#ffffff');

        $this->assertNotNull($message);
        $this->assertStringContainsString('3.13:1', $message);
        $this->assertStringContainsString('#ffffff', $message);
        $this->assertStringContainsString('#28a745', $message);
        $this->assertStringContainsString('will still use the colors you chose', $message);
    }
}
