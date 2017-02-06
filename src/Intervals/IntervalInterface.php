<?php
namespace JRBarnard\DateIntervalIterator\Intervals;

use DateTime;

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
     * Constants for every day of the week
     */
    const SUNDAY = 0;
    const MONDAY = 1;
    const TUESDAY = 2;
    const WEDNESDAY = 3;
    const THURSDAY = 4;
    const FRIDAY = 5;
    const SATURDAY = 6;

    /**
     * A constant wrapper to group the days of the week
     */
    const DAYS_OF_WEEK = [
        self::SUNDAY,
        self::MONDAY,
        self::TUESDAY,
        self::WEDNESDAY,
        self::THURSDAY,
        self::FRIDAY,
        self::SATURDAY
    ];

    /**
     * A constant wrapper for just the weekdays
     */
    const WEEKDAYS = [
        self::MONDAY,
        self::TUESDAY,
        self::WEDNESDAY,
        self::THURSDAY,
        self::FRIDAY,
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
