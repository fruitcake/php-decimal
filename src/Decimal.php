<?php

declare(strict_types=1);

namespace Fruitcake\Decimal;

use BcMath\Number;
use InvalidArgumentException;
use RuntimeException;

final class Decimal
{
    protected Number $value;

    protected int $precision;

    /**
     * Internal precision for calculations (higher than display precision for accuracy)
     */
    private const int INTERNAL_SCALE = 20;

    public function __construct(mixed $value, int $precision = 2)
    {
        $this->precision = $precision;
        $this->value = $this->toNumber($value);
    }

    public static function fromUnitValue(mixed $value, int $precision): self
    {
        $multiplier = new Number(10 ** $precision);
        $number = new Number((string) $value);
        $decimal = new self(0, $precision);
        $decimal->value = $number->div($multiplier, self::INTERNAL_SCALE);

        return $decimal;
    }

    /**
     * Parse the input from user input, with different comma/dot
     *
     * @param mixed $value
     * @param int $precision
     *
     * @return Decimal
     */
    public static function parseLocale(mixed $value, int $precision): self
    {
        if (!is_string($value)) {
            $value = (string) $value;
        }

        if ($value == '') {
            $value = '0.00';
        }
        // Check if start with a dot
        if (str_starts_with($value, '.')) {
            $value = '0' . $value;
        }

        $value = str_replace([' ', '+', '€'], '', $value);

        if (preg_match("/^-?[0-9]+(?:\.[0-9]{1,2})?$/", $value)) {
            return new Decimal($value, $precision);
        }

        $fmt = numfmt_create('nl_NL', \NumberFormatter::DECIMAL);

        $result = numfmt_parse($fmt, $value);
        if ($result === false) {
            // Not a valid locale value and no decimal, assume it's a normal float value
            if (is_numeric($value) && !str_contains($value, ',')) {
                return new Decimal($value, $precision);
            }

            throw new InvalidArgumentException('Cannot parse decimal value `' . $value . '`: ' . numfmt_get_error_message($fmt));
        }

        return new Decimal((string) $result, $precision);
    }

    public function isZero(): bool
    {
        // Check if value rounds to zero at display precision (backwards compatible)
        return $this->toString() === $this->roundToString(new Number('0'), $this->precision);
    }

    public function isPositive(): bool
    {
        // Check based on rounded display value for backwards compatibility
        if ($this->isZero()) {
            return false;
        }
        return $this->value->compare(new Number('0')) > 0;
    }

    public function isNegative(): bool
    {
        // Check based on rounded display value for backwards compatibility
        if ($this->isZero()) {
            return false;
        }
        return $this->value->compare(new Number('0')) < 0;
    }

    public function isZeroOrPositive(): bool
    {
        return $this->isZero() || $this->isPositive();
    }

    public function isZeroOrNegative(): bool
    {
        return $this->isZero() || $this->isNegative();
    }

    /**
     * Get the internal BcMath\Number value
     */
    public function getValue(): Number
    {
        return $this->value;
    }

    /**
     * Get the unit value (for backwards compatibility)
     * Returns the value multiplied by 10^precision as a string
     */
    public function getUnitValue(): string
    {
        $multiplier = new Number(10 ** $this->precision);
        $unitValue = $this->value->mul($multiplier);

        return $this->roundToString($unitValue, 0);
    }

    public function equals(mixed $value): bool
    {
        if (!($value instanceof Decimal)) {
            $value = new Decimal($value, $this->getPrecision());
        }

        $this->comparePrecision($value);

        // Compare at display precision
        return $this->toString() === $value->toString();
    }

    public function isBiggerThan(mixed $value): bool
    {
        if (!($value instanceof Decimal)) {
            $value = new Decimal($value, $this->getPrecision());
        }

        $this->comparePrecision($value);

        return $this->value->compare($value->getValue()) > 0;
    }

    public function isBiggerOrEqualThan(mixed $value): bool
    {
        if (!($value instanceof Decimal)) {
            $value = new Decimal($value, $this->getPrecision());
        }

        $this->comparePrecision($value);

        return $this->value->compare($value->getValue()) >= 0;
    }

    public function isSmallerThan(mixed $value): bool
    {
        if (!($value instanceof Decimal)) {
            $value = new Decimal($value, $this->getPrecision());
        }

        $this->comparePrecision($value);

        return $this->value->compare($value->getValue()) < 0;
    }

    public function isSmallerOrEqualThan(mixed $value): bool
    {
        if (!($value instanceof Decimal)) {
            $value = new Decimal($value, $this->getPrecision());
        }

        $this->comparePrecision($value);

        return $this->value->compare($value->getValue()) <= 0;
    }

    public function notEquals(mixed $value): bool
    {
        return !$this->equals($value);
    }

    public function add(mixed $value): self
    {
        if (!($value instanceof Decimal)) {
            $value = new Decimal($value, $this->getPrecision());
        }

        $this->comparePrecision($value);

        $result = new self(0, $this->getPrecision());
        $result->value = $this->value->add($value->getValue());

        return $result;
    }

    public function sub(mixed $value): self
    {
        if (!($value instanceof Decimal)) {
            $value = new Decimal($value, $this->getPrecision());
        }

        $this->comparePrecision($value);

        $result = new self(0, $this->getPrecision());
        $result->value = $this->value->sub($value->getValue());

        return $result;
    }

    public function multiply(mixed $multiplier): self
    {
        $multiplierNumber = $this->toNumber($multiplier);
        $result = new self(0, $this->getPrecision());
        $result->value = $this->value->mul($multiplierNumber);

        return $result;
    }

    public function divide(mixed $divisor): self
    {
        $divisorNumber = $this->toNumber($divisor);
        $result = new self(0, $this->getPrecision());
        $result->value = $this->value->div($divisorNumber, self::INTERNAL_SCALE);

        return $result;
    }

    public function toString(?int $precision = null): string
    {
        $displayPrecision = $precision ?? $this->getPrecision();

        return $this->roundToString($this->value, $displayPrecision);
    }

    public function __toString(): string
    {
        return $this->toString();
    }

    private function toNumber(mixed $value): Number
    {
        if ($value instanceof Number) {
            return $value;
        }

        if ($value instanceof Decimal) {
            return $value->getValue();
        }

        // Convert to string to avoid float precision issues
        if (is_float($value)) {
            // Handle very small numbers that would otherwise be in scientific notation
            // If the absolute value is less than 1e-14, treat as zero
            if (abs($value) < 1e-14) {
                $stringValue = '0';
            } else {
                // Use number_format to avoid scientific notation
                $stringValue = number_format($value, self::INTERNAL_SCALE, '.', '');
                // Trim trailing zeros after decimal point
                $stringValue = rtrim(rtrim($stringValue, '0'), '.');
                if ($stringValue === '' || $stringValue === '-') {
                    $stringValue = '0';
                }
            }
        } else {
            $stringValue = (string) $value;
        }

        return new Number($stringValue);
    }

    /**
     * Round a Number to a string with the given precision
     */
    private function roundToString(Number $number, int $precision): string
    {
        $rounded = $number->round($precision);

        // Format with the correct number of decimal places
        $str = (string) $rounded;

        // Handle the decimal formatting
        if ($precision === 0) {
            // For zero precision, return just the integer part
            $pos = strpos($str, '.');
            return $pos !== false ? substr($str, 0, $pos) : $str;
        }

        // Ensure we have the decimal point
        if (!str_contains($str, '.')) {
            $str .= '.';
        }

        // Pad with zeros to reach the required precision
        $pos = strpos($str, '.');
        $currentDecimals = strlen($str) - $pos - 1;

        if ($currentDecimals < $precision) {
            $str .= str_repeat('0', $precision - $currentDecimals);
        } elseif ($currentDecimals > $precision) {
            $str = substr($str, 0, $pos + $precision + 1);
        }

        return $str;
    }

    private function getPrecision(): int
    {
        return $this->precision;
    }

    private function comparePrecision(self $other): void
    {
        if ($other->getPrecision() !== $this->getPrecision()) {
            throw new RuntimeException('Precision must match');
        }
    }
}
