<?php declare(strict_types=1);

namespace Banner\Stdlib;

use InvalidArgumentException;

/**
 * WCAG 2.1 contrast-ratio helpers.
 *
 * The banner lets administrators choose their own text and background colors.
 * These helpers only measure the result so the module can warn when a choice
 * falls below the WCAG AA threshold for normal text (4.5:1); they never alter
 * the administrator's choice.
 *
 * @see https://www.w3.org/TR/WCAG21/#dfn-contrast-ratio
 * @see https://www.w3.org/TR/WCAG21/#dfn-relative-luminance
 */
class ColorContrast
{
    /**
     * WCAG AA minimum contrast ratio for normal-size text.
     */
    public const AA_NORMAL_TEXT = 4.5;

    /**
     * Whether a string is a #rgb or #rrggbb color.
     */
    public static function isValidColor(string $color): bool
    {
        return (bool) preg_match('/^#(?:[0-9a-fA-F]{3}|[0-9a-fA-F]{6})$/', $color);
    }

    /**
     * Contrast ratio between two colors, rounded to two decimals.
     *
     * @throws InvalidArgumentException When either color is not #rgb/#rrggbb.
     */
    public static function ratio(string $colorA, string $colorB): float
    {
        return round(self::rawRatio($colorA, $colorB), 2);
    }

    /**
     * Whether a text/background pair meets a contrast threshold.
     *
     * @throws InvalidArgumentException When either color is not #rgb/#rrggbb.
     */
    public static function meetsAa(
        string $backgroundColor,
        string $textColor,
        float $minimum = self::AA_NORMAL_TEXT
    ): bool {
        return self::rawRatio($backgroundColor, $textColor) >= $minimum;
    }

    /**
     * Relative luminance of a color, per WCAG 2.1.
     *
     * @throws InvalidArgumentException When the color is not #rgb/#rrggbb.
     */
    public static function luminance(string $color): float
    {
        [$red, $green, $blue] = self::channels($color);
        return 0.2126 * $red + 0.7152 * $green + 0.0722 * $blue;
    }

    /**
     * A warning message when a text/background pair is below WCAG AA, or null
     * when the pair passes.
     *
     * The module deliberately does not change the administrator's colors; this
     * message is what tells them the choice may be hard to read.
     */
    public static function warningMessage(string $backgroundColor, string $textColor): ?string
    {
        if (self::meetsAa($backgroundColor, $textColor)) {
            return null;
        }

        return sprintf(
            'The banner text color (%s) and background color (%s) have a contrast ratio of %.2f:1. '
            . 'WCAG AA requires at least 4.5:1 for normal-size text. The banner will still use the colors you chose, '
            . 'but some visitors may find the text hard to read.',
            $textColor,
            $backgroundColor,
            self::ratio($backgroundColor, $textColor)
        );
    }

    private static function rawRatio(string $colorA, string $colorB): float
    {
        $luminanceA = self::luminance($colorA);
        $luminanceB = self::luminance($colorB);
        $lighter = max($luminanceA, $luminanceB);
        $darker = min($luminanceA, $luminanceB);

        return ($lighter + 0.05) / ($darker + 0.05);
    }

    /**
     * Linearized sRGB channels for a hex color.
     *
     * @return array{0: float, 1: float, 2: float}
     */
    private static function channels(string $color): array
    {
        if (!self::isValidColor($color)) {
            throw new InvalidArgumentException(sprintf('Invalid hex color "%s".', $color));
        }

        $hex = ltrim($color, '#');
        if (3 === strlen($hex)) {
            $hex = $hex[0] . $hex[0] . $hex[1] . $hex[1] . $hex[2] . $hex[2];
        }

        $channels = [];
        foreach ([0, 2, 4] as $offset) {
            $value = hexdec(substr($hex, $offset, 2)) / 255;
            $channels[] = $value <= 0.03928
                ? $value / 12.92
                : (($value + 0.055) / 1.055) ** 2.4;
        }

        return $channels;
    }
}
