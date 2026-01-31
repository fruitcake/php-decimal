<?php

declare(strict_types=1);

use Fruitcake\Decimal\Decimal;

function decimal($value, $precision = 2): Decimal
{
    return new Decimal($value, $precision);
}

function decimal_parse_locale($value, $precision = 2, ?string $locale = null): Decimal
{
    return Decimal::parseLocale($value, $precision, $locale);
}
