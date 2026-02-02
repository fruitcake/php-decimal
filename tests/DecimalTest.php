<?php

declare(strict_types=1);

namespace Fruitcake\Decimal\Tests;

use Fruitcake\Decimal\Decimal;
use PHPUnit\Framework\TestCase;
use RuntimeException;

/**
 * @see Decimal
 */
class DecimalTest extends TestCase
{
    public function testConstruct()
    {
        $decimal = new Decimal(1.234, 3);

        $this->assertInstanceOf(Decimal::class, $decimal);
        $this->assertEquals('1234', $decimal->getUnitValue());

        $decimal = Decimal::parseLocale('12,34', 2);

        $this->assertInstanceOf(Decimal::class, $decimal);
        $this->assertEquals('1234', $decimal->getUnitValue());
        $this->assertEquals('12.34', $decimal->toString());
    }

    public function testFromUnitValue()
    {
        $decimal = Decimal::fromUnitValue(1234, 2);

        $this->assertInstanceOf(Decimal::class, $decimal);
        $this->assertEquals('1234', $decimal->getUnitValue());
        $this->assertEquals('12.34', $decimal->toString());
    }

    /**
     * @dataProvider provideLocaleFormats
     */
    public function testLocaleFormats($value, $expected)
    {
        $decimal = Decimal::parseLocale($value, 2);

        $this->assertSame($expected, $decimal->toString());
    }

    public static function provideLocaleFormats()
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
            ['€50', '50.00'],
            ['€50,00', '50.00'],
            ['€50,-', '50.00'],
            ['€50,01', '50.01'],
            ['€50.01', '50.01'],
        ];
    }

    /**
     * @dataProvider provideInvalidLocaleFormats
     */
    public function testInvalidLocaleFormats($value)
    {
        $this->expectException(\InvalidArgumentException::class);

        Decimal::parseLocale($value, 2);
    }

    public static function provideInvalidLocaleFormats()
    {
        return [
            ['a'],
        ];
    }

    public function testHelpers()
    {
        $decimal = decimal(1.23, 2);
        $this->assertInstanceOf(Decimal::class, $decimal);
        $this->assertSame('1.23', $decimal->toString());

        $decimal = decimal_parse_locale('1,23', 2);
        $this->assertInstanceOf(Decimal::class, $decimal);
        $this->assertSame('1.23', $decimal->toString());
    }

    public function testToString()
    {
        $decimal = new Decimal(1.23, 2);

        $this->assertSame('1.23', $decimal->toString());
        $this->assertSame('1.23', (string) $decimal);
    }

    /**
     *
     * @dataProvider provideDecimalFormats
     */
    public function testParseAndFormat($value, $formatted, $precision = 2)
    {
        $decimal = new Decimal($value, $precision);

        $this->assertSame($formatted, $decimal->toString());
    }

    public static function provideDecimalFormats()
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
     *
     * @dataProvider provideZeros
     */
    public function testIsZero($value, int $precision = 2)
    {
        $decimal = new Decimal($value, $precision);

        $this->assertTrue($decimal->isZero());
        $this->assertTrue($decimal->isZeroOrPositive());
        $this->assertTrue($decimal->isZeroOrNegative());
        $this->assertFalse($decimal->isPositive());
        $this->assertFalse($decimal->isNegative());
        $this->assertSame('0.00', (string) $decimal);
    }

    public static function provideZeros()
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
     *
     * @dataProvider providePositives
     */
    public function testIsPositive($value, int $precision = 2)
    {
        $decimal = new Decimal($value, $precision);

        $this->assertTrue($decimal->isPositive());
        $this->assertTrue($decimal->isZeroOrPositive());

        $this->assertFalse($decimal->isZeroOrNegative());
        $this->assertFalse($decimal->isZero());
        $this->assertFalse($decimal->isNegative());
    }

    public static function providePositives()
    {
        return [
            [1],
            ['0.01'],
            ['0.001', 3],
        ];
    }

    /**
     *
     * @dataProvider provideNegatives
     */
    public function testIsNegative($value, int $precision = 2)
    {
        $decimal = new Decimal($value, $precision);

        $this->assertTrue($decimal->isNegative());
        $this->assertTrue($decimal->isZeroOrNegative());

        $this->assertFalse($decimal->isZeroOrPositive());
        $this->assertFalse($decimal->isZero());
        $this->assertFalse($decimal->isPositive());
    }

    public static function provideNegatives()
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
    public function testAdd($a, $b, $expected, $precision = 2)
    {
        if ($precision == 2) {
            $this->assertSame($expected, decimal($a, $precision)->add($b)->toString());
        }
        $this->assertSame($expected, decimal($a, $precision)->add(decimal($b, $precision))->toString());
    }

    public static function provideAdds()
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
    public function testSub($a, $b, $expected, $precision = 2)
    {
        if ($precision == 2) {
            $this->assertSame($expected, decimal($a, $precision)->sub($b)->toString());
        }

        $this->assertSame($expected, decimal($a, $precision)->sub(decimal($b, $precision))->toString());
    }

    public static function provideSub()
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
    public function testMultiply($a, $b, $expected, $precision = 2)
    {
        $this->assertSame($expected, decimal($a, $precision)->multiply($b)->toString());
    }

    public static function provideMultiply()
    {
        return [
            [0.1, 2, '0.20'],
            ['2', '2', '4.00'],
            ['2.0', '2', '4', 0],
            ['1.0', '17.40', '17.40', 2],
            ['17.40', '1.00', '17.40', 2],
        ];
    }

    /**
     * @dataProvider provideEquals
     */
    public function testEquals($a, $b, $precision = 2)
    {
        if ($precision == 2) {
            $this->assertTrue(decimal($a, $precision)->equals($b));
        }
        $this->assertTrue(decimal($a, $precision)->equals(decimal($b, $precision)));
    }

    public static function provideEquals()
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
    public function testNoteEquals($a, $b, $precision = 2)
    {
        if ($precision == 2) {
            $this->assertFalse(decimal($a, $precision)->equals($b));
        }

        $this->assertFalse(decimal($a, $precision)->equals(decimal($b, $precision)));
    }

    public static function provideNotEquals()
    {
        return [
            [0.1, 0.12],
            [0.111, 0.112, 3, 3],
        ];
    }

    /**
     * @dataProvider provideDivide
     */
    public function testDivide($a, $b, $expected, $precision = 2)
    {
        $this->assertSame($expected, decimal($a, $precision)->divide($b)->toString());
    }

    public static function provideDivide()
    {
        return [
            [0.1, 2, '0.05'],
            ['2', '2', '1.00'],
            ['2.0', '2', '1', 0],
            ['2.0', '20', '0', 0],
            ['2.0', '20', '0.1', 1],
            ['2.0', '200', '0.0', 1],
        ];
    }

    /**
     * @see https://3v4l.org/ps346
     *
     */
    public function testFloatInconsistency()
    {
        $a = 0.17;
        $b = 1 - 0.83; //0.17
        $value = $a - $b;
        $this->assertFalse($value == 0);    // Floating point difference, we expect it to be zero
        $this->assertFalse($value == 0.00);

        $decimal = new Decimal($value);

        $this->assertTrue($decimal->isZero());
        $this->assertFalse($decimal->isPositive());
        $this->assertFalse($decimal->isPositive());
        $this->assertSame('0.00', (string) $decimal);

        $decimal = decimal($a)->sub($b);
        $this->assertEquals('0.00', $decimal->toString());
        $this->assertTrue($decimal->isZero());
    }

    public function testChaining()
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

    public function testPreservesInternalPrecision()
    {
        $a = decimal('2.30')->multiply('0.75')->toString();
        $this->assertEquals('1.73', $a);

        $b = decimal('2.30')->sub(decimal('2.30')->multiply('0.25'))->toString();
        $this->assertEquals('1.73', $b);
        $this->assertEquals($a, $b);
    }

    public function testDecimalIsNotEqual()
    {
        $this->assertTrue(decimal(3)->notEquals(5));
    }

    public function testIsBiggerThan()
    {
        $a = 3.52;
        $b = decimal(4.8798);

        $this->assertTrue($b->isBiggerThan($a));
    }

    public function testIsSmallerThan()
    {
        $a = 3.52;
        $b = decimal(2.8798);

        $this->assertTrue($b->isSmallerThan($a));
    }

    public function testIsBiggerOrEqualThan()
    {
        $a = 3.52;
        $b = decimal(4.8798);
        $c = decimal(4.8798);

        $this->assertTrue($b->isBiggerOrEqualThan($a));
        $this->assertTrue($b->isBiggerOrEqualThan($c));
    }

    public function testIsSmallerOrEqualThan()
    {
        $a = decimal(3.52);
        $b = decimal(2.8798);
        $c = 3.52;

        $this->assertTrue($a->isSmallerOrEqualThan($a));
        $this->assertTrue($b->isSmallerOrEqualThan($c));
    }
}
