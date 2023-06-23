<?php

namespace Bdf\PHPUnit\Comparator;

use DateTimeInterface;
use SebastianBergmann\Comparator\Comparator;
use SebastianBergmann\Comparator\ComparisonFailure;
use SebastianBergmann\Comparator\DateTimeComparator as BaseDateTimeComparator;

/**
 * DateTimeComparator
 */
class DateTimeComparator extends Comparator
{
    /**
     * @var BaseDateTimeComparator
     */
    private $comparator;

    public function __construct()
    {
        $this->comparator = new BaseDateTimeComparator();
    }

    /**
     * {@inheritdoc}
     */
    public function accepts($expected, $actual): bool
    {
        return $expected instanceof DateTimeInterface && $actual instanceof DateTimeInterface;
    }

    /**
     * {@inheritdoc}
     */
    public function assertEquals($expected, $actual, $delta = 0.0, $canonicalize = false, $ignoreCase = false, array &$processed = array()): void
    {
        try {
            $this->comparator->assertEquals($expected, $actual, $delta);
        } catch (ComparisonFailure $e) {
            $diff = $expected->diff($actual);

            if (! $this->isMicrotimeDiff($diff)) {
                throw $e;
            }
        }
    }

    /**
     * Calculate if the diff is due to microtime
     *
     * @param \DateInterval $diff
     *
     * @return bool
     */
    private function isMicrotimeDiff(\DateInterval $diff)
    {
        return $diff->days === 0
            && $diff->y === 0
            && $diff->m === 0
            && $diff->d === 0
            && $diff->h === 0
            && $diff->i === 0
            && $diff->s === 0;
    }
}