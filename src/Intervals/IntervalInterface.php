<?php
namespace JRBarnard\DateIntervalIterator\Intervals;

use DateTime;
use JRBarnard\DateIntervalIterator\DateIntervalIterator;

/**
 * Interface IntervalInterface
 * @package JRBarnard\DateIntervalIterator\Intervals
 */
interface IntervalInterface
{
    /**
     * Method that finds the next occurrence of the interval from current
     *
     * @param DateTime $current
     * @param DateIntervalIterator $iterator
     *
     * @return DateTime
     */
    public function findNextOccurrence(DateTime $current, DateIntervalIterator $iterator);
}