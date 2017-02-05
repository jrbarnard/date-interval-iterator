<?php
namespace JRBarnard\DateIntervalIterator\Intervals;

use DateTime;
use Symfony\Component\Translation\Interval;

/**
 * Interface IntervalInterface
 * @package JRBarnard\DateIntervalIterator\Intervals
 */
interface IntervalInterface
{
    /**
     * Forwards direction
     */
    const FORWARDS = 1;

    /**
     * Backwards direction
     */
    const BACKWARDS = 0;

    /**
     * Valid directions that can be used
     */
    const DIRECTIONS = [
        self::FORWARDS,
        self::BACKWARDS
    ];

    /**
     * Method that finds the next occurrence of the interval from current
     *
     * @param DateTime $current
     * @param $direction
     *
     * @return DateTime
     */
    public function findNextOccurrence(DateTime $current, $direction = self::FORWARDS);
}
