# Decimal class for PHP

[![Unit Tests](https://github.com/fruitcake/php-decimal/actions/workflows/run-tests.yml/badge.svg)](https://github.com/fruitcake/php-decimal/actions)
[![PHPStan Level 5](https://img.shields.io/badge/PHPStan-Level%205-blue)](https://github.com/fruitcake/php-decimal/actions)
[![Code Coverage](https://img.shields.io/badge/CodeCoverage-100%25-brightgreen)](https://github.com/fruitcake/php-decimal/actions/workflows/run-coverage.yml)
[![Packagist License](https://poser.pugx.org/fruitcake/php-decimal/license.png)](http://choosealicense.com/licenses/mit/)
[![Latest Stable Version](https://poser.pugx.org/fruitcake/php-decimal/version.png)](https://packagist.org/packages/fruitcake/php-decimal)
[![Total Downloads](https://poser.pugx.org/fruitcake/php-decimal/d/total.png)](https://packagist.org/packages/fruitcake/php-decimal)
[![Fruitcake](https://img.shields.io/badge/Powered%20By-Fruitcake-b2bc35.svg)](https://fruitcake.nl/)

Library for handling decimals in PHP

## Installation

Require `fruitcake/php-decimal` using composer.

### Example: using the library

```php
<?php

use Fruitcake\Decimal\Decimal;

$decimal = new Decimal('1');
$value = $decimal->sub('0.8');

echo $decimal->toString(2); // "0.20"
```

## License

Released under the MIT License, see [LICENSE](LICENSE).
