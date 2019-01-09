<?php

use JRBarnard\Recurrence\Intervals\MonthlyInterval;
use JRBarnard\Recurrence\Intervals\IntervalInterface;
use JRBarnard\Recurrence\Exceptions\BadMethodCallException;
use JRBarnard\Recurrence\Exceptions\InvalidArgumentException;

/**
 * Class MonthlyIntervalTest
 */
class MonthlyIntervalTest extends TestCase
{
//    const INTERVAL_CLASS = MonthlyInterval::class;

    /**
     * Tests:
     * TODO:
     *  - constructor
     *      - optional should accept one of frequency constants on MonthlyInterval
     *      - optional should accept array of days of week as second param
     *      - optional should accept number of months as third param
     *  - setDays
     *      - will return self
     *      - will set days prop
     *      - will throw if invalid days
     *  - getDays
     *      - will return array of days set
     *  - setFrequency
     *      - will accept any of the frequency constants on MonthlyInterval and set frequency prop
     *      - will throw if invalid
     *      - will return self
     *  - getFrequency
     *      - will return set frequency constant
     *  - setMonths
     *      - will set months prop
     *      - will return self
     *      - will throw if not int or > 0
     *  - getMonths
     *      - will get months set
     *      - will default to 1
     *  - findNextOccurrence
     *      - will find next occurrence based on set criteria (using provider with multiple data points)
     *      - will work backwards
     *  - every fluent setter
     *      - will accept frequency and days as params and call setFrequency and setDays
     *      - will return self
     *  - ofEveryMonth fluent setter
     *      - will accept int and pass to setMonth
     *      - will return self
     *  - every{Frequency} fluent setter
     *      - will set frequency based on frequency used (e.g everyLast)
     *      - will throw if invalid
     *      - will call setFrequency and pass through frequency
     *      - will return self
     *  - {day constant} fluent setter
     *      - will throw if invalid day
     *      - will pass through to setDays
     *      - will return self
     *  - and{day constant} fluent setter
     *      - will throw if invalid day constant
     *      - will get days then add passed day and set unique days to setDays
     *      - will return self
     *  - ofEvery{2nd->12th}Month fluent setter
     *      - will throw if invalid
     *      - will pass parsed regularity of months to setMonths
     *      - will return self
     */

}
