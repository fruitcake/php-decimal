<?php

declare(strict_types=1);

namespace Fruitcake\Decimal;

final class Decimal
{
    protected string $value;

    protected int $precision = 2;

    public function __construct(mixed $value, int $precision = 2)
    {
        $this->precision = $precision;
        $this->setUnitValue($value * $this->getMultiplier());
    }

    public static function fromUnitValue(mixed $value, int $precision): self
    {
        $decimal = new Decimal(0.0, $precision);
        $decimal->setUnitValue($value);

        return $decimal;
    }

    /**
     * Parse the input from user input, with different comma/dot
     *
     * @param mixed $value
     * @param $precision
     *
     * @return Decimal
     */
    public static function parseLocale(mixed $value, $precision): self
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

        $value = str_replace([' ', '+', 'â‚¬'], '', $value);

        if (preg_match("/^-?[0-9]+(?:\.[0-9]{1,2})?$/", $value)) {
            return new Decimal((float) $value, $precision);
        }

        $fmt = numfmt_create('nl_NL', \NumberFormatter::DECIMAL);

        $result = numfmt_parse($fmt, $value);
        if ($result === false) {
            // Not a valid locale value and no decimal, assume it's a normal float value
            if (is_numeric($value) && !str_contains($value, ',')) {
                return new Decimal((float) $value, $precision);
            }

            throw new \InvalidArgumentException('Cannot parse decimal value `' . $value . '`: ' . numfmt_get_error_message($fmt));
        }

        return new Decimal($result, $precision);
    }

    public function isZero(): bool
    {
        return $this->getUnitValue() == '0';
    }

    public function isPositive(): bool
    {
        return $this->getUnitValue() > 0;
    }

    public function isNegative(): bool
    {
        return $this->getUnitValue() < 0;
    }

    public function isZeroOrPositive(): bool
    {
        return $this->isZero() || $this->isPositive();
    }

    public function isZeroOrNegative(): bool
    {
        return $this->isZero() || $this->isNegative();
    }

    public function getUnitValue(): mixed
    {
        return $this->value;
    }

    public function equals(mixed $value): bool
    {
        if (!($value instanceof Decimal)) {
            $value = new Decimal($value, $this->getPrecision());
        }

        $this->comparePrecision($value);

        return $this->getUnitValue() === $value->getUnitValue();
    }

    public function isBiggerThan(mixed $value): bool
    {
        if (!($value instanceof Decimal)) {
            $value = new Decimal($value, $this->getPrecision());
        }

        $this->comparePrecision($value);

        return $this->getUnitValue() > $value->getUnitValue();
    }

    public function isBiggerOrEqualThan(mixed $value): bool
    {
        if (!($value instanceof Decimal)) {
            $value = new Decimal($value, $this->getPrecision());
        }

        $this->comparePrecision($value);

        return $this->getUnitValue() >= $value->getUnitValue();
    }

    public function isSmallerThan(mixed $value): bool
    {
        if (!($value instanceof Decimal)) {
            $value = new Decimal($value, $this->getPrecision());
        }

        $this->comparePrecision($value);

        return $this->getUnitValue() < $value->getUnitValue();
    }

    public function isSmallerOrEqualThan(mixed $value): bool
    {
        if (!($value instanceof Decimal)) {
            $value = new Decimal($value, $this->getPrecision());
        }

        $this->comparePrecision($value);

        return $this->getUnitValue() <= $value->getUnitValue();
    }

    public function notEquals(mixed $value): bool
    {
        if (!($value instanceof Decimal)) {
            $value = new Decimal($value, $this->getPrecision());
        }

        $this->comparePrecision($value);

        return $this->getUnitValue() !== $value->getUnitValue();
    }

    public function add(mixed $value): self
    {
        if (!($value instanceof Decimal)) {
            $value = new Decimal($value, $this->getPrecision());
        }

        $this->comparePrecision($value);

        return Decimal::fromUnitValue($this->getUnitValue() + $value->getUnitValue(), $this->getPrecision());
    }

    public function sub(mixed $value): self
    {
        if (!($value instanceof Decimal)) {
            $value = new Decimal($value, $this->getPrecision());
        }

        $this->comparePrecision($value);

        return Decimal::fromUnitValue($this->getUnitValue() - $value->getUnitValue(), $this->getPrecision());
    }

    public function multiply(mixed $multiplier): self
    {
        return Decimal::fromUnitValue($this->getUnitValue() * $multiplier, $this->getPrecision());
    }

    public function divide(mixed $division): self
    {
        return Decimal::fromUnitValue($this->getUnitValue() / $division, $this->getPrecision());
    }

    public function toString(int $precision = null): string
    {
        return $this->parseAsString($this->getUnitValue() / $this->getMultiplier(), $precision);
    }

    public function __toString()
    {
        return $this->toString();
    }

    private function parseAsString(mixed $value, int $precision = null): string
    {
        return number_format($value, !is_null($precision) ? $precision : $this->getPrecision(), '.', '');
    }

    private function getPrecision(): int
    {
        return $this->precision;
    }

    private function getMultiplier(): int
    {
        return 10 ** $this->getPrecision();
    }

    private function setUnitValue(mixed $value): void
    {
        $this->value = number_format($value, 0, '', '');
    }

    private function comparePrecision(self $other): void
    {
        if ($other->getPrecision() !== $this->getPrecision()) {
            throw new \RuntimeException('Precision must match');
        }
    }
}
