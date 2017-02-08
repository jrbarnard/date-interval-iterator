<?php

use JRBarnard\Recurrence\Iterator;
use JRBarnard\Recurrence\Intervals\IntervalInterface;

/**
 * Class TestCase
 */
class TestCase extends PHPUnit_Framework_TestCase
{
    /**
     * Extracted to a const just in case is changed during development
     */
    const ITERATOR_CLASS = Iterator::class;

    /**
     * General format we will use
     */
    const DATE_TIME_FORMAT = 'Y-m-d H:i:s';

    /**
     * @param null $start
     * @param null $interval
     * @param null $endAfter
     *
     * @param int $direction
     *
     * @return Iterator
     */
    protected function generateIterator($start = null, $interval = null, $endAfter = null, $direction = IntervalInterface::FORWARDS)
    {
        if (!$interval instanceof IntervalInterface) {
            $interval = new TestInterval();
        }

        if (is_null($start)) {
            $start = new DateTime();
        }

        if (is_null($endAfter)) {
            $endAfter = 10;
        }

        $class = self::ITERATOR_CLASS;

        return new $class($start, $interval, $endAfter, $direction);
    }
}