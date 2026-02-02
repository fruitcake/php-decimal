<?php

declare(strict_types=1);

use Brick\Math\BigDecimal;
use Fruitcake\Decimal\Decimal;

function decimal(Decimal|int|float|string $value, int $precision = 2): Decimal
{
    return new Decimal($value, $precision);
}

function decimal_parse_locale(int|float|string $value, int $precision = 2): Decimal
{
    return Decimal::parseLocale($value, $precision);
}
