<?php

declare(strict_types=1);

namespace Fruitcake\Decimal\Tests;

use BcMath\Number;
use Fruitcake\Decimal\Decimal;
use PHPUnit\Framework\TestCase;
use RuntimeException;

/**
 * @see Decimal
 */
class DecimalTest extends TestCase
{
    public function testConstruct(): void
    {
        $decimal = new Decimal(1.234, 3);

        $this->assertInstanceOf(Decimal::class, $decimal);
        $this->assertEquals('1234', $decimal->getUnitValue());

        $decimal = Decimal::parseLocale('12,34', 2);

        $this->assertInstanceOf(Decimal::class, $decimal);
        $this->assertEquals('1234', $decimal->getUnitValue());
        $this->assertEquals('12.34', $decimal->toString());
    }

    public function testFromUnitValue(): void
    {
        $decimal = Decimal::fromUnitValue(1234, 2);

        $this->assertInstanceOf(Decimal::class, $decimal);
        $this->assertEquals('1234', $decimal->getUnitValue());
        $this->assertEquals('12.34', $decimal->toString());
    }

    public function testGetValue(): void
    {
        $decimal = new Decimal('12.34', 2);

        $this->assertInstanceOf(Number::class, $decimal->getValue());
    }

    /**
     * @dataProvider provideLocaleFormats
     */
    public function testLocaleFormats(mixed $value, string $expected): void
    {
        $decimal = Decimal::parseLocale($value, 2);

        $this->assertSame($expected, $decimal->toString());
    }

    public static function provideLocaleFormats(): array
    {
        return [
            ['', '0.00'],
            ['0', '0.00'],
            [0, '0.00'],
            [1, '1.00'],
            ['-0', '0.00'],
            [.2, '0.20'],
            ['.2', '0.20'],
            ['.25', '0.25'],
            [0.2, '0.20'],
            ['+0.2', '0.20'],
            ['-0.2', '-0.20'],
            [-0.2, '-0.20'],
            [+0.2, '0.20'],
            ['-1', '-1.00'],
            ['- 2', '-2.00'],
            ['- 2.00', '-2.00'],
            ['- 2. 15', '-2.15'],
            ['€59,-', '59.00'],
            ['1', '1.00'],
            [1.2, '1.20'],
            ['1.2', '1.20'],
            [1.00, '1.00'],
            ['1,23', '1.23'],
            ['1.23', '1.23'],
            ['12,34', '12.34'],
            ['1234', '1234.00'],
            ['1234,00', '1234.00'],
            ['123,45', '123.45'],
            ['1234,56', '1234.56'],
            ['1234.56', '1234.56'],
            ['1.234,56', '1234.56'],
            ['1.234.567', '1234567.00'],
            ['1.234.567,00', '1234567.00'],
            ['12.345.678,12', '12345678.12'],
            ['-12.345.678,12', '-12345678.12'],
            ['100.000', '100000.00'],
            ['-100.000', '-100000.00'],
            ['50.30000000000000', '50.30'],
            ['50.80000000000001', '50.80'],
        ];
    }

    /**
     * @dataProvider provideInvalidLocaleFormats
     */
    public function testInvalidLocaleFormats(mixed $value): void
    {
        $this->expectException(\InvalidArgumentException::class);

        Decimal::parseLocale($value, 2);
    }

    public static function provideInvalidLocaleFormats(): array
    {
        return [
            ['a'],
        ];
    }

    public function testHelpers(): void
    {
        $decimal = decimal(1.23, 2);
        $this->assertInstanceOf(Decimal::class, $decimal);
        $this->assertSame('1.23', $decimal->toString());

        $decimal = decimal_parse_locale('1,23', 2);
        $this->assertInstanceOf(Decimal::class, $decimal);
        $this->assertSame('1.23', $decimal->toString());
    }

    public function testToString(): void
    {
        $decimal = new Decimal(1.23, 2);

        $this->assertSame('1.23', $decimal->toString());
        $this->assertSame('1.23', (string) $decimal);
    }

    public function testToStringWithDifferentPrecision(): void
    {
        $decimal = new Decimal('1.23456', 5);

        $this->assertSame('1.23456', $decimal->toString());
        $this->assertSame('1.23', $decimal->toString(2));
        $this->assertSame('1.2346', $decimal->toString(4));
        $this->assertSame('1', $decimal->toString(0));
    }

    /**
     * @param mixed $value
     * @param string $formatted
     * @param int $precision
     *
     * @dataProvider provideDecimalFormats
     */
    public function testParseAndFormat(mixed $value, string $formatted, int $precision = 2): void
    {
        $decimal = new Decimal($value, $precision);

        $this->assertSame($formatted, $decimal->toString());
    }

    public static function provideDecimalFormats(): array
    {
        return [
            [1, '1.00'],
            ['1', '1.00'],
            [1.00, '1.00'],
            ['1.0', '1.00'],
            ['1.23', '1.23'],
            [1.23, '1.23'],
            [1.234, '1.23'],
            [1.345, '1.35'],
            [1234567890.1234567890, '1234567890.1235', 4],
            [(1 - 0.83), '0.17'],
            [0, '0.00'],
            ['0', '0.00'],
            ['0.001', '0.00'],
        ];
    }

    /**
     * @param mixed $value
     * @param int $precision
     *
     * @dataProvider provideZeros
     */
    public function testIsZero(mixed $value, int $precision = 2): void
    {
        $decimal = new Decimal($value, $precision);

        $this->assertTrue($decimal->isZero());
        $this->assertTrue($decimal->isZeroOrPositive());
        $this->assertTrue($decimal->isZeroOrNegative());
        $this->assertFalse($decimal->isPositive());
        $this->assertFalse($decimal->isNegative());
        $this->assertSame('0.00', (string) $decimal);
    }

    public static function provideZeros(): array
    {
        return [
            [0],
            ['0'],
            ['0.0'],
            ['0.00'],
            ['0.001'],
        ];
    }

    /**
     * @param mixed $value
     * @param int $precision
     *
     * @dataProvider providePositives
     */
    public function testIsPositive(mixed $value, int $precision = 2): void
    {
        $decimal = new Decimal($value, $precision);

        $this->assertTrue($decimal->isPositive());
        $this->assertTrue($decimal->isZeroOrPositive());

        $this->assertFalse($decimal->isZeroOrNegative());
        $this->assertFalse($decimal->isZero());
        $this->assertFalse($decimal->isNegative());
    }

    public static function providePositives(): array
    {
        return [
            [1],
            ['0.01'],
            ['0.001', 3],
        ];
    }

    /**
     * @param mixed $value
     * @param int $precision
     *
     * @dataProvider provideNegatives
     */
    public function testIsNegative(mixed $value, int $precision = 2): void
    {
        $decimal = new Decimal($value, $precision);

        $this->assertTrue($decimal->isNegative());
        $this->assertTrue($decimal->isZeroOrNegative());

        $this->assertFalse($decimal->isZeroOrPositive());
        $this->assertFalse($decimal->isZero());
        $this->assertFalse($decimal->isPositive());
    }

    public static function provideNegatives(): array
    {
        return [
            [-1],
            ['-1'],
            ['-0.01'],
            ['-0.001', 3],
        ];
    }

    /**
     * @dataProvider provideAdds
     */
    public function testAdd(mixed $a, mixed $b, string $expected, int $precision = 2): void
    {
        if ($precision == 2) {
            $this->assertSame($expected, decimal($a, $precision)->add($b)->toString());
        }
        $this->assertSame($expected, decimal($a, $precision)->add(decimal($b, $precision))->toString());
    }

    public static function provideAdds(): array
    {
        return [
            [0.1, 0.2, '0.30'],
            ['1', '2.0', '3.00'],
            ['1', '2.0', '3.0', 1],
        ];
    }

    /**
     * @dataProvider provideSub
     */
    public function testSub(mixed $a, mixed $b, string $expected, int $precision = 2): void
    {
        if ($precision == 2) {
            $this->assertSame($expected, decimal($a, $precision)->sub($b)->toString());
        }

        $this->assertSame($expected, decimal($a, $precision)->sub(decimal($b, $precision))->toString());
    }

    public static function provideSub(): array
    {
        return [
            [0.1, 0.2, '-0.10'],
            ['1', '2.0', '-1.00'],
            ['2.0', '1', '1', 0],
        ];
    }

    /**
     * @dataProvider provideMultiply
     */
    public function testMultiply(mixed $a, mixed $b, string $expected, int $precision = 2): void
    {
        $this->assertSame($expected, decimal($a, $precision)->multiply($b)->toString());
    }

    public static function provideMultiply(): array
    {
        return [
            [0.1, 2, '0.20'],
            ['2', '2', '4.00'],
            ['2.0', '2', '4', 0],
        ];
    }

    /**
     * @dataProvider provideEquals
     */
    public function testEquals(mixed $a, mixed $b, int $precision = 2): void
    {
        if ($precision == 2) {
            $this->assertTrue(decimal($a, $precision)->equals($b));
        }
        $this->assertTrue(decimal($a, $precision)->equals(decimal($b, $precision)));
    }

    public static function provideEquals(): array
    {
        return [
            [1, '1'],
            ['1.00', '1'],
            [0.111, 0.112, 2],
        ];
    }

    /**
     * @dataProvider provideNotEquals
     */
    public function testNotEquals(mixed $a, mixed $b, int $precision = 2): void
    {
        if ($precision == 2) {
            $this->assertFalse(decimal($a, $precision)->equals($b));
        }

        $this->assertFalse(decimal($a, $precision)->equals(decimal($b, $precision)));
    }

    public static function provideNotEquals(): array
    {
        return [
            [0.1, 0.12],
            [0.111, 0.112, 3],
        ];
    }

    /**
     * @dataProvider provideDivide
     */
    public function testDivide(mixed $a, mixed $b, string $expected, int $precision = 2): void
    {
        $this->assertSame($expected, decimal($a, $precision)->divide($b)->toString());
    }

    public static function provideDivide(): array
    {
        return [
            [0.1, 2, '0.05'],
            ['2', '2', '1.00'],
            ['2.0', '2', '1', 0],
        ];
    }

    /**
     * Test high precision division
     */
    public function testHighPrecisionDivision(): void
    {
        // 1/3 should be accurate internally, but display based on precision
        $decimal = decimal(1, 10)->divide(3);

        $this->assertSame('0.3333333333', $decimal->toString());
        $this->assertSame('0.33', $decimal->toString(2));
    }

    /**
     * @see https://3v4l.org/ps346
     *
     */
    public function testFloatInconsistency(): void
    {
        $a = 0.17;
        $b = 1 - 0.83; //0.17
        $value = $a - $b;
        $this->assertFalse($value == 0);    // Floating point difference, we expect it to be zero
        $this->assertFalse($value == 0.00);

        $decimal = new Decimal($value);

        $this->assertTrue($decimal->isZero());
        $this->assertFalse($decimal->isPositive());
        $this->assertFalse($decimal->isNegative());
        $this->assertSame('0.00', (string) $decimal);

        $decimal = decimal($a)->sub($b);
        $this->assertEquals('0.00', $decimal->toString());
        $this->assertTrue($decimal->isZero());
    }

    public function testChaining(): void
    {
        $a = decimal(3.00)->sub(0.5)->add(0.01);
        $this->assertEquals('2.51', $a->toString());
        $this->assertTrue($a->equals(2.51));

        $b = decimal(3.00)->sub(0.5)->sub(0.1);
        $this->assertTrue($b->equals(2.4));
        $this->assertTrue($b->equals('2.400'));

        $c = decimal(3.00)->sub(0.5)->add('-2.5');
        $this->assertTrue($c->isZero());
        $this->assertTrue($c->isZeroOrNegative());
    }

    public function testDecimalIsNotEqual(): void
    {
        $this->assertTrue(decimal(3)->notEquals(5));
    }

    public function testComparePrecision(): void
    {
        $this->expectException(RuntimeException::class);

        $a = decimal(3.00, 3);
        $b = decimal(5.00, 5);

        $a->equals($b);
    }

    public function testIsBiggerThan(): void
    {
        $a = 3.52;
        $b = decimal(4.8798);

        $this->assertTrue($b->isBiggerThan($a));
    }

    public function testIsSmallerThan(): void
    {
        $a = 3.52;
        $b = decimal(2.8798);

        $this->assertTrue($b->isSmallerThan($a));
    }

    public function testIsBiggerOrEqualThan(): void
    {
        $a = 3.52;
        $b = decimal(4.8798);
        $c = decimal(4.8798);

        $this->assertTrue($b->isBiggerOrEqualThan($a));
        $this->assertTrue($b->isBiggerOrEqualThan($c));
    }

    public function testIsSmallerOrEqualThan(): void
    {
        $a = decimal(3.52);
        $b = decimal(2.8798);
        $c = 3.52;

        $this->assertTrue($a->isSmallerOrEqualThan($a));
        $this->assertTrue($b->isSmallerOrEqualThan($c));
    }

    /**
     * Test that BcMath\Number provides higher internal precision
     */
    public function testInternalPrecisionHigherThanDisplay(): void
    {
        // Perform calculation that would lose precision with floats
        $a = decimal('1', 2);
        $result = $a->divide(3)->multiply(3);

        // Should be 1.00 due to internal high precision, not 0.99
        $this->assertSame('1.00', $result->toString());
    }

    /**
     * Test very large numbers
     */
    public function testLargeNumbers(): void
    {
        $decimal = new Decimal('99999999999999999999.99', 2);

        $this->assertSame('99999999999999999999.99', $decimal->toString());
        $this->assertTrue($decimal->isPositive());
    }

    /**
     * Test very small numbers with high precision
     */
    public function testVerySmallNumbers(): void
    {
        $decimal = new Decimal('0.00000001', 8);

        $this->assertSame('0.00000001', $decimal->toString());
        $this->assertTrue($decimal->isPositive());
        $this->assertFalse($decimal->isZero());
    }

    /**
     * Test multiplication with price and duration
     */
    public function testConstructWithNegativeValue(): void
    {
        $durationInHours = '1.00';
        $price = '17.40';

        $total = decimal($price)->multiply($durationInHours)->toString();
        $this->assertEquals('17.40', $total);

        $total = decimal($durationInHours)->multiply($price)->toString();
        $this->assertEquals('17.40', $total);
    }

    /**
     * Test percentage calculation
     */
    public function testPercentage(): void
    {
        $total = decimal('2.30')->sub(decimal('2.30')->multiply('0.25'))->toString();
        $total2 = decimal('2.30')->multiply('0.75')->toString();

        $this->assertEquals('1.73', $total2);
        $this->assertEquals('1.73', $total);
    }

    /**
     * Test BcMath\Number as constructor input
     */
    public function testConstructWithNumber(): void
    {
        $number = new Number('123.45');
        $decimal = new Decimal($number, 2);

        $this->assertSame('123.45', $decimal->toString());
        $this->assertSame('12345', $decimal->getUnitValue());
    }

    /**
     * Test BcMath\Number in arithmetic operations
     */
    public function testArithmeticWithNumber(): void
    {
        $decimal = new Decimal('10.00', 2);
        $number = new Number('2.5');

        $this->assertSame('12.50', $decimal->add($number)->toString());
        $this->assertSame('7.50', $decimal->sub($number)->toString());
        $this->assertSame('25.00', $decimal->multiply($number)->toString());
        $this->assertSame('4.00', $decimal->divide($number)->toString());
    }

    /**
     * Test BcMath\Number in comparison operations
     */
    public function testComparisonWithNumber(): void
    {
        $decimal = new Decimal('10.00', 2);
        $bigger = new Number('15');
        $smaller = new Number('5');
        $equal = new Number('10');

        $this->assertTrue($decimal->isSmallerThan($bigger));
        $this->assertTrue($decimal->isBiggerThan($smaller));
        $this->assertTrue($decimal->equals($equal));
    }

    /**
     * Test compare() method returns -1, 0, 1
     */
    public function testCompareMethod(): void
    {
        $decimal = new Decimal('10.00', 2);

        $this->assertSame(-1, $decimal->compare('15.00'));
        $this->assertSame(0, $decimal->compare('10.00'));
        $this->assertSame(1, $decimal->compare('5.00'));
    }

    /**
     * Test comparison consistency at display precision
     */
    public function testComparisonConsistency(): void
    {
        // 1.004 and 1.003 both round to 1.00
        $a = new Decimal('1.004', 2);
        $b = new Decimal('1.003', 2);

        // Both round to 1.00, so should be equal
        $this->assertTrue($a->equals($b));
        $this->assertFalse($a->isBiggerThan($b));
        $this->assertFalse($a->isSmallerThan($b));
        $this->assertTrue($a->isBiggerOrEqualThan($b));
        $this->assertTrue($a->isSmallerOrEqualThan($b));

        // 1.006 rounds to 1.01, so should NOT be equal to 1.004 (1.00)
        $c = new Decimal('1.006', 2);
        $this->assertFalse($a->equals($c));
        $this->assertTrue($a->isSmallerThan($c));
    }

    /**
     * Test abs() method
     */
    public function testAbs(): void
    {
        $positive = new Decimal('5.50', 2);
        $negative = new Decimal('-5.50', 2);
        $zero = new Decimal('0.00', 2);

        $this->assertSame('5.50', $positive->abs()->toString());
        $this->assertSame('5.50', $negative->abs()->toString());
        $this->assertSame('0.00', $zero->abs()->toString());
    }

    /**
     * Test negate() method
     */
    public function testNegate(): void
    {
        $positive = new Decimal('5.50', 2);
        $negative = new Decimal('-5.50', 2);
        $zero = new Decimal('0.00', 2);

        $this->assertSame('-5.50', $positive->negate()->toString());
        $this->assertSame('5.50', $negative->negate()->toString());
        $this->assertSame('0.00', $zero->negate()->toString());
    }

    /**
     * Test division by zero throws exception
     */
    public function testDivisionByZero(): void
    {
        $this->expectException(\DivisionByZeroError::class);

        decimal('10.00')->divide('0');
    }

    /**
     * Test parseLocale with custom locale
     */
    public function testParseLocaleWithCustomLocale(): void
    {
        // German format: 1.234,56
        $decimal = Decimal::parseLocale('1.234,56', 2, 'de_DE');
        $this->assertSame('1234.56', $decimal->toString());

        // US format: 1,234.56
        $decimal = Decimal::parseLocale('1,234.56', 2, 'en_US');
        $this->assertSame('1234.56', $decimal->toString());
    }

    /**
     * Test negative precision (rounding to tens, hundreds)
     */
    public function testNegativePrecision(): void
    {
        $decimal = new Decimal('1234.56', 2);

        $this->assertSame('1235', $decimal->toString(0));
        $this->assertSame('1230', $decimal->toString(-1));
        $this->assertSame('1200', $decimal->toString(-2));
    }
}
