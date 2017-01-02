<?php
namespace DateIntervalIterator\Intervals;

use DateTime;
use DateIntervalIterator\DateIntervalIterator;

/**
 * Interface IntervalInterface
 * @package DateIntervalIterator\Intervals
 */
interface IntervalInterface
{
    /**
     * Method that finds the next occurrence of the interval from current
     * @param DateTime $current
     * @param DateIntervalIterator $iterator
     * @return mixed
     */
    public function findNextOccurrence(DateTime $current, DateIntervalIterator $iterator);
}