# PHP Decimal

A simple decimal class for PHP that avoids floating-point precision issues by using `BcMath\Number` internally.

## Requirements

- PHP 8.4+
- ext-bcmath
- ext-intl

## Installation

```bash
composer require fruitcake/php-decimal
```

## Usage

```php
use Fruitcake\Decimal\Decimal;

// Create a decimal with precision 2
$decimal = new Decimal(1.23, 2);

// Or use the helper
$decimal = decimal(1.23);

// Arithmetic operations
$result = $decimal->add(0.5);      // 1.73
$result = $decimal->sub(0.5);      // 0.73
$result = $decimal->multiply(2);   // 2.46
$result = $decimal->divide(2);     // 0.62

// Comparisons
$decimal->equals(1.23);            // true
$decimal->isBiggerThan(1.00);      // true
$decimal->isSmallerThan(2.00);     // true

// Parse locale-formatted values (nl_NL)
$decimal = Decimal::parseLocale('1.234,56', 2);  // 1234.56

// Output
echo $decimal->toString();         // "1.23"
echo $decimal->toString(4);        // "1.2300" (custom precision)
echo (string) $decimal;            // "1.23"

// Access internal BcMath\Number
$number = $decimal->getValue();    // BcMath\Number instance
```

## Why BcMath\Number?

PHP's native float type can cause precision issues:

```php
$a = 0.1 + 0.2;
var_dump($a == 0.3); // false!
```

This library uses PHP 8.4's `BcMath\Number` class internally, which provides arbitrary-precision arithmetic. The precision parameter controls output formatting, while internal calculations maintain higher precision.

## License

MIT
