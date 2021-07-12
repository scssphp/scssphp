<?php

/**
 * SCSSPHP
 *
 * @copyright 2012-2020 Leaf Corcoran
 *
 * @license http://opensource.org/licenses/MIT MIT
 *
 * @link http://scssphp.github.io/scssphp
 */

namespace ScssPhp\ScssPhp\Value;

use ScssPhp\ScssPhp\Colors;
use ScssPhp\ScssPhp\Exception\SassScriptException;
use ScssPhp\ScssPhp\Util\ErrorUtil;
use ScssPhp\ScssPhp\Util\NumberUtil;
use ScssPhp\ScssPhp\Util\SerializationUtil;

final class SassColor extends Value
{
    /**
     * This color's red channel, between `0` and `255`.
     *
     * @var int|null
     */
    private $red;

    /**
     * This color's blue channel, between `0` and `255`.
     *
     * @var int|null
     */
    private $blue;

    /**
     * This color's green channel, between `0` and `255`.
     *
     * @var int|null
     */
    private $green;

    /**
     * This color's hue, between `0` and `360`.
     *
     * @var int|float|null
     */
    private $hue;

    /**
     * This color's saturation, a percentage between `0` and `100`.
     *
     * @var int|float|null
     */
    private $saturation;

    /**
     * This color's lightness, a percentage between `0` and `100`.
     *
     * @var int|float|null
     */
    private $lightness;

    /**
     * TThis color's alpha channel, between `0` and `1`.
     *
     * @var int|float
     */
    private $alpha;

    /**
     * Creates a RGB color
     *
     * @param int $red
     * @param int $blue
     * @param int $green
     * @param int|float|null $alpha
     *
     * @return SassColor
     *
     * @throws \OutOfRangeException if values are outside the expected range.
     */
    public static function rgb(int $red, int $green, int $blue, $alpha = null): SassColor
    {
        if ($alpha === null) {
            $alpha = 1;
        } else {
            $alpha = NumberUtil::fuzzyAssertRange($alpha, 0, 1, 'alpha');
        }

        ErrorUtil::checkIntInInterval($red, 0, 255, 'red');
        ErrorUtil::checkIntInInterval($green, 0, 255, 'green');
        ErrorUtil::checkIntInInterval($blue, 0, 255, 'blue');

        return new self($red, $blue, $green, null, null, null, $alpha);
    }

    /**
     * @param int|float $hue
     * @param int|float $saturation
     * @param int|float $lightness
     * @param int|float|null $alpha
     *
     * @return SassColor
     */
    public static function hsl($hue, $saturation, $lightness, $alpha = null): SassColor
    {
        if ($alpha === null) {
            $alpha = 1;
        } else {
            $alpha = NumberUtil::fuzzyAssertRange($alpha, 0, 1, 'alpha');
        }

        $hue = fmod($hue , 360);
        $saturation = NumberUtil::fuzzyAssertRange($saturation, 0, 100, 'saturation');
        $lightness = NumberUtil::fuzzyAssertRange($lightness, 0, 100, 'lightness');

        return new self(null, null, null, $hue, $saturation, $lightness, $alpha);
    }

    /**
     * @param int|float      $hue
     * @param int|float      $whiteness
     * @param int|float      $blackness
     * @param int|float|null $alpha
     *
     * @return SassColor
     */
    public static function hwb($hue, $whiteness, $blackness, $alpha = null): SassColor
    {
        $scaledHue = fmod($hue , 360) / 360;
        $scaledWhiteness = NumberUtil::fuzzyAssertRange($whiteness, 0, 100, 'whiteness') / 100;
        $scaledBlackness = NumberUtil::fuzzyAssertRange($blackness, 0, 100, 'blackness') / 100;

        $sum = $scaledWhiteness + $scaledBlackness;

        if ($sum > 1) {
            $scaledWhiteness /= $sum;
            $scaledBlackness /= $sum;
        }

        $factor = 1 - $scaledWhiteness - $scaledBlackness;

        $toRgb = function (float $hue) use ($factor, $scaledWhiteness) {
            $channel = self::hueToRgb(0, 1, $hue) * $factor + $scaledWhiteness;

            return NumberUtil::fuzzyRound($channel * 255);
        };

        return self::rgb($toRgb($scaledHue + 1/3), $toRgb($scaledHue), $toRgb($scaledHue - 1/3), $alpha);
    }

    /**
     * This must always provide non-null values for either RGB or HSL values.
     * If they are all provided, they are expected to be in sync and this not
     * revalidated. This constructor does not revalidate ranges either.
     * Use named factories when this cannot be guaranteed.
     *
     * @param int|null       $red
     * @param int|null       $green
     * @param int|null       $blue
     * @param int|float|null $hue
     * @param int|float|null $saturation
     * @param int|float|null $lightness
     * @param int|float      $alpha
     */
    private function __construct(?int $red, ?int $green, ?int $blue, $hue, $saturation, $lightness, $alpha)
    {
        $this->red = $red;
        $this->blue = $blue;
        $this->green = $green;
        $this->hue = $hue;
        $this->saturation = $saturation;
        $this->lightness = $lightness;
        $this->alpha = $alpha;
    }

    public function getRed(): int
    {
        if (\is_null($this->red)) {
            $this->hslToRgb();
            assert(!\is_null($this->red));
        }

        return $this->red;
    }

    public function getGreen(): int
    {
        if (\is_null($this->green)) {
            $this->hslToRgb();
            assert(!\is_null($this->green));
        }

        return $this->green;
    }

    public function getBlue(): int
    {
        if (\is_null($this->blue)) {
            $this->hslToRgb();
            assert(!\is_null($this->blue));
        }

        return $this->blue;
    }

    /**
     * @return int|float
     */
    public function getHue()
    {
        if (\is_null($this->hue)) {
            $this->rgbToHsl();
            assert(!\is_null($this->hue));
        }

        return $this->hue;
    }

    /**
     * @return int|float
     */
    public function getSaturation()
    {
        if (\is_null($this->saturation)) {
            $this->rgbToHsl();
            assert(!\is_null($this->saturation));
        }

        return $this->saturation;
    }

    /**
     * @return int|float
     */
    public function getLightness()
    {
        if (\is_null($this->lightness)) {
            $this->rgbToHsl();
            assert(!\is_null($this->lightness));
        }

        return $this->lightness;
    }

    /**
     * @return float|int
     */
    public function getWhiteness()
    {
        return min($this->getRed(), $this->getGreen(), $this->getBlue()) / 255 * 100;
    }

    /**
     * @return float|int
     */
    public function getBlackness()
    {
        return 100 - max($this->getRed(), $this->getGreen(), $this->getBlue()) / 255 * 100;
    }

    /**
     * @return int|float
     */
    public function getAlpha()
    {
        return $this->alpha;
    }

    /**
     * @param int|null $red
     * @param int|null $green
     * @param int|null $blue
     * @param int|float|null $alpha
     *
     * @return SassColor
     */
    public function changeRgb(?int $red = null, ?int $green = null, ?int $blue = null, $alpha = null): SassColor
    {
        $red = !\is_null($red) ? $red : $this->getRed();
        $green = !\is_null($green) ? $green : $this->getGreen();
        $blue = !\is_null($blue) ? $blue : $this->getBlue();
        $alpha = !\is_null($alpha) ? $alpha : $this->alpha;

        return self::rgb($red, $green, $blue, $alpha);
    }

    /**
     * @param int|float|null $hue
     * @param int|float|null $saturation
     * @param int|float|null $lightness
     * @param int|float|null $alpha
     *
     * @return SassColor
     */
    public function changeHsl($hue = null, $saturation = null, $lightness = null, $alpha = null): SassColor
    {
        $hue = !\is_null($hue) ? $hue : $this->getHue();
        $saturation = !\is_null($saturation) ? $saturation : $this->getSaturation();
        $lightness = !\is_null($lightness) ? $lightness : $this->getLightness();
        $alpha = !\is_null($alpha) ? $alpha : $this->alpha;

        return self::hsl($hue, $saturation, $lightness, $alpha);
    }

    /**
     * @param int|float|null $hue
     * @param int|float|null $whiteness
     * @param int|float|null $blackness
     * @param int|float|null $alpha
     *
     * @return SassColor
     */
    public function changeHwb($hue = null, $whiteness = null, $blackness = null, $alpha = null): SassColor
    {
        $hue = !\is_null($hue) ? $hue : $this->getHue();
        $whiteness = !\is_null($whiteness) ? $whiteness : $this->getWhiteness();
        $blackness = !\is_null($blackness) ? $blackness : $this->getBlackness();
        $alpha = !\is_null($alpha) ? $alpha : $this->alpha;

        return self::hwb($hue, $whiteness, $blackness, $alpha);
    }

    /**
     * @param int|float $alpha
     *
     * @return SassColor
     */
    public function changeAlpha($alpha): SassColor
    {
        return new self(
            $this->red,
            $this->green,
            $this->blue,
            $this->hue,
            $this->saturation,
            $this->lightness,
            NumberUtil::fuzzyAssertRange($alpha, 0, 1, 'alpha')
        );
    }

    public function plus(Value $other): Value
    {
        if (!$other instanceof SassColor && !$other instanceof SassNumber) {
            return parent::plus($other);
        }

        throw new SassScriptException("Undefined operation \"$this + $other\".");
    }

    public function minus(Value $other): Value
    {
        if (!$other instanceof SassColor && !$other instanceof SassNumber) {
            return parent::minus($other);
        }

        throw new SassScriptException("Undefined operation \"$this - $other\".");
    }

    public function dividedBy(Value $other): Value
    {
        if (!$other instanceof SassColor && !$other instanceof SassNumber) {
            return parent::dividedBy($other);
        }

        throw new SassScriptException("Undefined operation \"$this / $other\".");
    }

    public function modulo(Value $other): Value
    {
        if (!$other instanceof SassColor && !$other instanceof SassNumber) {
            return parent::modulo($other);
        }

        throw new SassScriptException("Undefined operation \"$this % $other\".");
    }

    public function equals(Value $other): bool
    {
        return $other instanceof SassColor && $this->getRed() === $other->getRed() && $this->getGreen() === $other->getGreen() && $this->getBlue() === $other->getBlue() && $this->alpha === $other->alpha;
    }

    public function toCssString(): string
    {
        return $this->__toString();
    }

    public function __toString(): string
    {
        $colorName = Colors::RGBaToColorName($this->getRed(), $this->getGreen(), $this->getBlue(), $this->alpha);

        if ($colorName !== null && !NumberUtil::fuzzyEquals($this->alpha, 0)) {
            return $colorName;
        }

        if (NumberUtil::fuzzyEquals($this->alpha, 1)) {
            return '#' . self::getHexComponent($this->getRed()) . self::getHexComponent($this->getGreen()) . self::getHexComponent($this->getBlue());
        }

        return 'rgba(' . $this->getRed() . ', ' . $this->getGreen() . ', ' . $this->getBlue() . ', ' .SerializationUtil::serializeNumber(
                $this->alpha
            ).')';
    }

    /**
     * @param int $colorComponent
     *
     * @return string
     */
    private static function getHexComponent(int $colorComponent): string
    {
        return str_pad(dechex($colorComponent), 2, '0', STR_PAD_LEFT);
    }

    /**
     * @return void
     */
    private function rgbToHsl(): void
    {
        $scaledRed = $this->getRed() / 255;
        $scaledGreen = $this->getGreen() / 255;
        $scaledBlue = $this->getBlue() / 255;

        $min = min($scaledRed, $scaledGreen, $scaledBlue);
        $max = max($scaledRed, $scaledGreen, $scaledBlue);
        $delta = $max - $min;

        if ($delta == 0) {
            $this->hue = 0;
        } elseif ($max == $scaledRed) {
            $this->hue = fmod(60 * ($scaledGreen - $scaledBlue) / $delta, 360);
        } elseif ($max == $scaledGreen) {
            $this->hue = fmod(120 + 60 * ($scaledBlue - $scaledRed) / $delta, 360);
        } else {
            $this->hue = fmod(240 + 60 * ($scaledRed - $scaledGreen) / $delta, 360);
        }

        $this->lightness = 50 * ($max + $min);

        if ($max == $min) {
            $this->saturation = 50;
        } elseif ($this->lightness < 50) {
            $this->saturation = 100 * $delta / ($max + $min);
        } else {
            $this->saturation = 100 * $delta / (2 - $max - $min);
        }
    }

    /**
     * @return void
     */
    private function hslToRgb(): void
    {
        $scaledHue = $this->getHue() / 360;
        $scaledSaturation = $this->getSaturation() / 100;
        $scaledLightness = $this->getLightness() / 100;

        if ($scaledLightness <= 0.5) {
            $m2 = $scaledLightness * ($scaledSaturation + 1);
        } else {
            $m2 = $scaledLightness + $scaledSaturation - $scaledLightness * $scaledSaturation;
        }

        $m1 = $scaledLightness * 2 - $m2;

        $this->red = self::hueToRgb($m1, $m2, $scaledHue + 1 / 3);
        $this->green = self::hueToRgb($m1, $m2, $scaledHue);
        $this->blue = self::hueToRgb($m1, $m2, $scaledHue - 1 / 3);
    }

    /**
     * @param int|float $m1
     * @param int|float $m2
     * @param int|float $h
     *
     * @return int
     */
    private static function hueToRgb($m1, $m2, $h): int
    {
        if ($h < 0) {
            $h += 1;
        } elseif ($h > 1) {
            $h -= 1;
        }

        if ($h < 1 / 6) {
            $result =  $m1 + ($m2 - $m1) * $h * 6;
        } elseif ($h < 1 / 2) {
            $result = $m2;
        } elseif ($h < 2 / 3) {
            $result = $m1 + ($m2 - $m1) * (2 / 3 - $h) * 6;
        } else {
            $result = $m1;
        }

        return NumberUtil::fuzzyRound($result * 255);
    }
}
