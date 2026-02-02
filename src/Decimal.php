<?php

declare(strict_types=1);

namespace Fruitcake\Decimal;

use Brick\Math\BigDecimal;
use Brick\Math\RoundingMode;

final readonly class Decimal
{
    private BigDecimal $bigDecimal;
    private int $precision;

    public function __construct(self|BigDecimal|int|float|string $value, int $precision = 2)
    {
        $this->precision = $precision;

        if ($value instanceof self) {
            $this->bigDecimal = clone $value->getInternalDecimal();
        } elseif ($value instanceof BigDecimal) {
            $this->bigDecimal = clone $value;
        } else {
            $this->bigDecimal = BigDecimal::of((string) $value)->toScale($precision, RoundingMode::HALF_UP);
        }
    }

    public static function fromUnitValue(int|float|string $value, int $precision): self
    {
        return new self(BigDecimal::ofUnscaledValue($value, $precision));
    }

    /**
     * Parse the input from user input, with different comma/dot
     *
     */
    public static function parseLocale(int|float|string $value, int $precision = 2): self
    {
        if (!is_string($value)) {
            $value = (string) $value;
        }

        if ($value === '') {
            $value = '0.00';
        }
        // Check if start with a dot
        if (str_starts_with($value, '.')) {
            $value = '0' . $value;
        }

        $value = str_replace([' ', '+', '€'], '', $value);

        if (preg_match("/^-?[0-9]+(?:\.[0-9]{1,2})?$/", $value) === 1) {
            return new Decimal((float) $value, $precision);
        }

        $fmt = numfmt_create('nl_NL', \NumberFormatter::DECIMAL);

        $result = $fmt !== null ? numfmt_parse($fmt, $value) : false;
        if ($result === false) {
            // Not a valid locale value and no decimal, assume it's a normal float value
            if (is_numeric($value) && !str_contains($value, ',')) {
                return new Decimal((float) $value, $precision);
            }

            throw new \InvalidArgumentException('Cannot parse decimal value `' . $value . '`: ' . ($fmt !== null ? numfmt_get_error_message($fmt) : 'Not NumberFormatter available'));
        }

        return new Decimal($result, $precision);
    }

    public function isZero(): bool
    {
        return $this->bigDecimal->isZero();
    }

    public function isPositive(): bool
    {
        return $this->bigDecimal->isPositive();
    }

    public function isNegative(): bool
    {
        return $this->bigDecimal->isNegative();
    }

    public function isZeroOrPositive(): bool
    {
        return $this->bigDecimal->isPositiveOrZero();
    }

    public function isZeroOrNegative(): bool
    {
        return $this->bigDecimal->isNegativeOrZero();
    }

    public function getUnitValue(): mixed
    {
        return $this->bigDecimal->getUnscaledValue();
    }

    public function equals(self|int|float|string $value): bool
    {
        return $this->bigDecimal->isEqualTo($this->prepareValue($value));
    }

    public function isBiggerThan(self|int|float|string $value): bool
    {
        return $this->bigDecimal->isGreaterThan($this->prepareValue($value));
    }

    public function isBiggerOrEqualThan(self|int|float|string $value): bool
    {
        return $this->bigDecimal->isGreaterThanOrEqualTo($this->prepareValue($value));
    }

    public function isSmallerThan(self|int|float|string $value): bool
    {
        return $this->bigDecimal->isLessThan($this->prepareValue($value));
    }

    public function isSmallerOrEqualThan(self|int|float|string $value): bool
    {
        return $this->bigDecimal->isLessThanOrEqualTo($this->prepareValue($value));
    }

    public function notEquals(self|int|float|string $value): bool
    {
        return !$this->bigDecimal->isEqualTo($this->prepareValue($value));
    }

    public function add(self|int|float|string $value): self
    {
        return new self($this->bigDecimal->plus($this->prepareValue($value)), $this->precision);
    }

    public function sub(self|int|float|string $value): self
    {
        return new self($this->bigDecimal->minus($this->prepareValue($value)), $this->precision);
    }

    public function multiply(self|int|float|string $multiplier): self
    {
        return new self($this->bigDecimal->multipliedBy($this->prepareValue($multiplier)), $this->precision);
    }

    public function divide(self|int|float|string $division): self
    {
        $result = $this->bigDecimal->dividedBy($this->prepareValue($division), null, RoundingMode::HALF_UP);

        return new self($result, $this->precision);
    }

    public function toString(?int $precision = null): string
    {
        $precision = $precision ?? $this->precision;

        return (string) $this->bigDecimal->toScale($precision, RoundingMode::HALF_UP);
    }

    public function __toString()
    {
        return $this->toString();
    }

    private function prepareValue(self|BigDecimal|int|float|string $value): BigDecimal
    {
        if ($value instanceof BigDecimal) {
            return $value;
        }

        if ($value instanceof self) {
            return $value->getInternalDecimal();
        }

        return (new self($value))->getInternalDecimal();
    }

    private function getInternalDecimal(): BigDecimal
    {
        return $this->bigDecimal;
    }
}
