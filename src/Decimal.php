<?php

declare(strict_types=1);

namespace Fruitcake\Decimal;

use Brick\Math\BigDecimal;
use Brick\Math\RoundingMode;

final class Decimal
{
    protected BigDecimal $bigDecimal;
    protected int $precision = 2;

    public function __construct(mixed $value, int $precision = 2)
    {
        $this->precision = $precision;

        if ($value instanceof self) {
            $this->bigDecimal = clone $value->getInternalDecimal();
        } elseif ($value instanceof BigDecimal) {
            $this->bigDecimal = clone $value;
        } else {
            $this->bigDecimal = BigDecimal::of($value)->toScale($precision, RoundingMode::HALF_UP);
        }
    }

    public static function fromUnitValue(mixed $value, int $precision): self
    {
        return new self(BigDecimal::ofUnscaledValue($value, $precision));
    }

    /**
     * Parse the input from user input, with different comma/dot
     *
     * @param string $value
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

    public function equals(mixed $value): bool
    {
        return $this->bigDecimal->isEqualTo($this->prepareValue($value));
    }

    public function isBiggerThan(mixed $value): bool
    {
        return $this->bigDecimal->isGreaterThan($this->prepareValue($value));
    }

    public function isBiggerOrEqualThan(mixed $value): bool
    {
        return $this->bigDecimal->isGreaterThanOrEqualTo($this->prepareValue($value));
    }

    public function isSmallerThan(mixed $value): bool
    {
        return $this->bigDecimal->isLessThan($this->prepareValue($value));
    }

    public function isSmallerOrEqualThan(mixed $value): bool
    {
        return $this->bigDecimal->isLessThanOrEqualTo($this->prepareValue($value));
    }

    public function notEquals(mixed $value): bool
    {
        return !$this->equals($this->prepareValue($value));
    }

    public function add(mixed $value): self
    {
        return new self($this->bigDecimal->plus($this->prepareValue($value)), $this->precision);
    }

    public function sub(mixed $value): self
    {
        return new self($this->bigDecimal->minus($this->prepareValue($value)), $this->precision);
    }

    public function multiply(mixed $multiplier): self
    {
        return new self($this->bigDecimal->multipliedBy($this->prepareValue($multiplier)), $this->precision);
    }

    public function divide(mixed $division): self
    {
        return new self($this->bigDecimal->dividedBy($this->prepareValue($division)), $this->precision);
    }

    public function toString(int $precision = null): string
    {
        $precision = $precision ?? $this->precision;

        return (string) $this->bigDecimal->toScale($precision, RoundingMode::HALF_UP);
   }

    public function __toString()
    {
        return $this->toString();
    }

    protected function prepareValue($value)
    {
        return $value instanceof self ? $value->getInternalDecimal() : (new static($value))->getInternalDecimal();
    }

    protected function getInternalDecimal()
    {
        return $this->bigDecimal;
    }
}
