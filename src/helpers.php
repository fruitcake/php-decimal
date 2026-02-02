<?php

declare(strict_types=1);

use Fruitcake\Decimal\Decimal;

function decimal(mixed $value, int $precision = 2): Decimal
{
    return new Decimal($value, $precision);
}

function decimal_parse_locale(mixed $value, int $precision = 2): Decimal
{
    return Decimal::parseLocale($value, $precision);
}
