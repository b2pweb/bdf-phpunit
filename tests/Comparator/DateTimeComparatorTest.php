<?php

namespace Bdf\PHPUnit\Comparator;

use DateTime;
use PHPUnit\Framework\TestCase;
use SebastianBergmann\Comparator\ComparisonFailure;

/**
 *
 */
class DateTimeComparatorTest extends TestCase
{
    /**
     *
     */
    public function test_accepts()
    {
        $comparator = new DateTimeComparator();

        $this->assertTrue($comparator->accepts(new DateTime(), new DateTime()));
        $this->assertFalse($comparator->accepts(new DateTime(), 'string'));
        $this->assertFalse($comparator->accepts('string', new DateTime()));
    }

    /**
     *
     */
    public function test_assert_is_equals()
    {
        $comparator = new DateTimeComparator();

        $expected = new DateTime('2017-06-28T12:32:26');
        $actual = new DateTime('2017-06-28T12:32:26');

        $result = $comparator->assertEquals($expected, $actual);

        $this->assertNull($result);
    }

    /**
     *
     */
    public function test_assert_is_not_equals()
    {
        $this->expectException(ComparisonFailure::class);

        $comparator = new DateTimeComparator();

        $expected = new DateTime('2017-06-28T12:32:27');
        $actual = new DateTime('2017-06-28T12:32:26');

        $result = $comparator->assertEquals($expected, $actual);

        $this->assertNull($result);
    }

    /**
     *
     */
    public function test_assert_is_not_equals_with_delta()
    {
        $comparator = new DateTimeComparator();

        $expected = new DateTime('2017-06-28T12:32:27');
        $actual = new DateTime('2017-06-28T12:32:26');

        $result = $comparator->assertEquals($expected, $actual, 1);

        $this->assertNull($result);
    }

    /**
     *
     */
    public function test_assert_is_equals_with_microtime()
    {
        $comparator = new DateTimeComparator();

        $expected = new DateTime('2017-06-28T12:32:26.568602+0000');
        $actual = new DateTime('2017-06-28T12:32:26.000000+0000');

        $result = $comparator->assertEquals($expected, $actual);

        $this->assertNull($result);
    }
}
