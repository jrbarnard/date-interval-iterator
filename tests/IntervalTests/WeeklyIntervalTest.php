<?php

use JRBarnard\DateIntervalIterator\Intervals\WeeklyInterval;
use JRBarnard\DateIntervalIterator\Exceptions\InvalidArgumentException;

/**
 * Class WeeklyIntervalTest
 */
class WeeklyIntervalTest extends TestCase
{
    const INTERVAL_CLASS = WeeklyInterval::class;

    // Tests:
    // Interval constructor will call setDays and setWeeks
    // setDays accepts valid days of the week - done
    // setDays accepts either single day of the week or array - done
    // setDays will return interval - done
    // if set same days via setDays, will only set one - done
    // all days must be valid days (stored as consts) - will throw if not - done
    // setWeeks accepts an int greater than 0, will store in weeks - done
    // if setWeeks not passed int greater than 0 will throw - done
    // can getWeeks using method, will get set - done
    // can getDays using method, will get set - done
    // getDays will return sorted days - done
    // Find next occurrence will work with relevant set days and weeks - done
    // Can run backwards
    // Can use magic setters and helper setters
    //  - everyTuesday, everyWednesday will overwrite
    //  - everyTuesday andEveryWednesday will append
    //  - call andEveryWednesday twice will not add twice
    //  - ofEvery3rdWeek, ofEveryWeek,
    //  - ofEveryWeek will accept number of weeks
    //  - TODO: MORE

    /** @test */
    public function if_set_same_days_will_only_set_once()
    {
        $days = [
            WeeklyInterval::SATURDAY,
            WeeklyInterval::SATURDAY,
            WeeklyInterval::SUNDAY,
        ];

        $interval = $this->generateWeeklyInterval();
        $interval->setDays($days);

        // Check not set duplicates
        $class = new ReflectionObject($interval);
        $property = $class->getProperty('days');
        $property->setAccessible(true);

        $actuallySet = $property->getValue($interval);
        $this->assertCount(2, $actuallySet);

        $alreadySet = [];
        foreach($actuallySet as $day) {
            // Fail if we already found it
            if (in_array($day, $alreadySet)) {
                $this->fail('Duplicate day found: ' . $day . ' in: ' . json_encode($alreadySet));
            }
            $alreadySet[] = $day;
        }
    }

    /** @test */
    public function setDays_will_store_sorted_days()
    {
        $days = [
            WeeklyInterval::SATURDAY, // 6
            WeeklyInterval::WEDNESDAY, // 3
            WeeklyInterval::MONDAY, // 1
            WeeklyInterval::THURSDAY, // 4
        ];

        $expectedOrder = [
            WeeklyInterval::MONDAY, // 1
            WeeklyInterval::WEDNESDAY, // 3
            WeeklyInterval::THURSDAY, // 4
            WeeklyInterval::SATURDAY, // 6
        ];

        $interval = $this->generateWeeklyInterval();

        $interval->setDays($days);

        // Check set in order
        $class = new ReflectionObject($interval);
        $property = $class->getProperty('days');
        $property->setAccessible(true);

        $this->assertSame($expectedOrder, $property->getValue($interval));
    }

    /**
     * @dataProvider findNextOccurrenceBackwardsProvider
     *
     * @test
     *
     * @param $start
     * @param $days
     * @param $weeks
     * @param $expected
     */
    public function findNextOccurrenceWorksBackwards($expected, $days, $weeks, $start)
    {
        $interval = $this->generateWeeklyInterval();

        $interval->setDays($days);
        $interval->setWeeks($weeks);

        $next = $interval->findNextOccurrence($start, WeeklyInterval::BACKWARDS);

        $this->assertSame($expected->getTimestamp(), $next->getTimestamp());
    }

    /**
     * @dataProvider findNextOccurrenceProvider
     * @test
     *
     * @param $start
     * @param $days
     * @param $weeks
     * @param $expected
     */
    public function findNextOccurrence_will_take_into_account_set_days_and_weeks_and_return_next_occurrence($start, $days, $weeks, $expected)
    {
        $interval = $this->generateWeeklyInterval();

        $interval->setDays($days);
        $interval->setWeeks($weeks);

        $next = $interval->findNextOccurrence($start);

        $this->assertSame($expected->getTimestamp(), $next->getTimestamp());
    }

    /** @test */
    public function getDays_will_return_set_days()
    {
        $interval = $this->generateWeeklyInterval();

        $expected = WeeklyInterval::THURSDAY;
        $interval->setDays($expected);

        $this->assertSame([$expected], $interval->getDays());
    }

    /** @test */
    public function getWeeks_will_return_set_weeks()
    {
        $interval = $this->generateWeeklyInterval();

        $expected = 9;
        $interval->setWeeks($expected);

        $this->assertSame($expected, $interval->getWeeks());
    }

    /** @test */
    public function setWeeks_accepts_int_number_of_weeks_greater_than_0_will_store_in_weeks()
    {
        $interval = $this->generateWeeklyInterval();

        $expected = 9;
        $interval->setWeeks($expected);

        $class = new ReflectionObject($interval);
        $property = $class->getProperty('weeks');
        $property->setAccessible(true);

        $this->assertSame($expected, $property->getValue($interval));
    }

    /**
     * @dataProvider invalidWeeksProvider
     * @test
     *
     * @param $invalidWeeks
     */
    public function setWeeks_will_throw_if_passed_non_int($invalidWeeks)
    {
        $interval = $this->generateWeeklyInterval();

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage(
            'You must pass a valid number of weeks to the interval'
        );

        $interval->setWeeks($invalidWeeks);
    }

    /** @test */
    public function setDays_will_return_interval()
    {
        $interval = $this->generateWeeklyInterval();

        $result = $interval->setDays(WeeklyInterval::THURSDAY);

        $this->assertInstanceOf(WeeklyInterval::class, $result);
    }

    /** @test */
    public function setDays_accepts_an_array_of_days()
    {
        $interval = $this->generateWeeklyInterval();

        $expected = [
            WeeklyInterval::THURSDAY,
            WeeklyInterval::FRIDAY
        ];
        $interval->setDays($expected);

        $class = new ReflectionObject($interval);
        $property = $class->getProperty('days');
        $property->setAccessible(true);

        $this->assertSame($expected, $property->getValue($interval));
    }

    /** @test */
    public function setDays_accepts_a_single_day_of_the_week()
    {
        $interval = $this->generateWeeklyInterval();

        $expected = WeeklyInterval::THURSDAY;
        $interval->setDays($expected);

        $class = new ReflectionObject($interval);
        $property = $class->getProperty('days');
        $property->setAccessible(true);

        $this->assertSame([$expected], $property->getValue($interval));
    }

    /**
     * @dataProvider invalidDaysProvider
     * @test
     * @param $invalidDays
     */
    public function can_set_days_will_only_accept_valid_days_stored_as_consts($invalidDays)
    {
        $interval = $this->generateWeeklyInterval();

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage(
            'You must pass valid days of the week to the interval'
        );
        $interval->setDays($invalidDays);
    }

    /**
     * HELPERS & PROVIDERS
     */

    /**
     * @return array
     */
    public function findNextOccurrenceProvider()
    {
        return [
            [
                new DateTime('2012-04-19 12:00:00'),
                [
                    WeeklyInterval::MONDAY,
                    WeeklyInterval::THURSDAY
                ],
                1,
                new DateTime('2012-04-23 12:00:00'),
            ],
            [
                new DateTime('2012-04-19 12:00:00'),
                [
                    WeeklyInterval::SUNDAY,
                    WeeklyInterval::SATURDAY // expected next
                ],
                2,
                new DateTime('2012-04-21 12:00:00'),
            ],
            [
                new DateTime('2012-04-19 12:00:00'),
                WeeklyInterval::FRIDAY,
                3,
                new DateTime('2012-04-20 12:00:00')
            ],
            [
                new DateTime('2012-04-19 12:00:00'),
                [
                    WeeklyInterval::THURSDAY,
                    WeeklyInterval::MONDAY,
                ],
                4,
                new DateTime('2012-05-14 12:00:00')
            ],
            [
                new DateTime('2012-04-19 12:00:00'),
                WeeklyInterval::THURSDAY,
                5,
                new DateTime('2012-05-24 12:00:00'),
            ],
            [
                new DateTime('2012-04-19 12:00:00'),
                [
                    WeeklyInterval::MONDAY
                ],
                52,
                new DateTime('2013-04-15 12:00:00'),
            ],
            [
                new DateTime('2012-04-20 12:00:00'),
                WeeklyInterval::WEEKDAYS,
                1,
                new DateTime('2012-04-23 12:00:00'),
            ],
            // 2017-01-01 - 0
            // [0,1,2,3,4,5,6]
            // NEXT 1 (2017-01-02)
            // PREV 6 (2016-12-31)
            [
                new DateTime('2017-01-01'),
                WeeklyInterval::DAYS_OF_WEEK,
                1,
                new DateTime('2017-01-02'),
            ],
            [
                new DateTime('2016-12-31'),
                WeeklyInterval::DAYS_OF_WEEK,
                1,
                new DateTime('2017-01-01'),
            ],
            // 2017-01-04 - 3
            // [0,1,2,3,4,5,6]
            // NEXT 4 (2017-01-05)
            // PREV 2 (2017-01-03)
            [
                new DateTime('2017-01-04'),
                WeeklyInterval::DAYS_OF_WEEK,
                1,
                new DateTime('2017-01-05'),
            ],
            // 2017-01-04 - 3
            // [0,1,2]
            // NEXT 0 (2017-01-08)
            // PREV 2 (2017-01-03)
            [
                new DateTime('2017-01-04'),
                [
                    WeeklyInterval::SUNDAY,
                    WeeklyInterval::MONDAY,
                    WeeklyInterval::TUESDAY
                ],
                1,
                new DateTime('2017-01-08'),
            ],
            // 2017-01-04 - 3
            // [0,1,2,3]
            // NEXT 0 (2017-01-08)
            // PREV 2 (2017-01-03)
            [
                new DateTime('2017-01-04'),
                [
                    WeeklyInterval::SUNDAY,
                    WeeklyInterval::MONDAY,
                    WeeklyInterval::TUESDAY,
                    WeeklyInterval::WEDNESDAY
                ],
                1,
                new DateTime('2017-01-08'),
            ],
            // 2017-01-04 - 3
            // [3,4,5]
            // NEXT 4 (2017-01-05)
            // PREV 5 (2017-12-30)
            [
                new DateTime('2017-01-04'),
                [
                    WeeklyInterval::WEDNESDAY,
                    WeeklyInterval::THURSDAY,
                    WeeklyInterval::FRIDAY
                ],
                1,
                new DateTime('2017-01-05'),
            ],
        ];
    }

    /**
     * @return array
     */
    public function findNextOccurrenceBackwardsProvider()
    {
        return [
            // 2017-01-04 - 3
            // [3,4,5]
            // NEXT 4 (2017-01-05)
            // PREV 5 (2016-12-30)
            [
                new DateTime('2016-12-30'),
                [
                    WeeklyInterval::WEDNESDAY,
                    WeeklyInterval::THURSDAY,
                    WeeklyInterval::FRIDAY
                ],
                1,
                new DateTime('2017-01-04'),
            ],
            // 2017-01-04 - 3
            // [0,1,2,3]
            // NEXT 0 (2017-01-08)
            // PREV 2 (2017-01-03)
            [
                new DateTime('2017-01-03'),
                [
                    WeeklyInterval::SUNDAY,
                    WeeklyInterval::MONDAY,
                    WeeklyInterval::TUESDAY
                ],
                1,
                new DateTime('2017-01-04'),
            ],
            // 2017-01-04 - 3
            // [0,1,2]
            // NEXT 0 (2017-01-08)
            // PREV 2 (2017-01-03)
            [
                new DateTime('2017-01-03'),
                [
                    WeeklyInterval::SUNDAY,
                    WeeklyInterval::MONDAY,
                    WeeklyInterval::TUESDAY
                ],
                1,
                new DateTime('2017-01-04'),
            ],
            // 2017-01-01 - 0
            // [0,1,2,3,4,5,6]
            // NEXT 1 (2017-01-02)
            // PREV 6 (2016-12-31)
            [
                new DateTime('2017-01-03'),
                WeeklyInterval::DAYS_OF_WEEK,
                1,
                new DateTime('2017-01-04'),
            ],
            [
                new DateTime('2012-04-15 12:00:00'),
                [
                    WeeklyInterval::SUNDAY,
                    WeeklyInterval::SATURDAY
                ],
                2,
                new DateTime('2012-04-21 12:00:00'),
            ],
            [
                new DateTime('2012-04-20 12:00:00'),
                WeeklyInterval::FRIDAY,
                3,
                new DateTime('2012-04-21 12:00:00')
            ],
            [
                new DateTime('2012-04-19 12:00:00'),
                [
                    WeeklyInterval::THURSDAY,
                    WeeklyInterval::MONDAY,
                ],
                4,
                new DateTime('2012-05-14 12:00:00')
            ],
            [
                new DateTime('2012-04-19 12:00:00'),
                WeeklyInterval::THURSDAY,
                5,
                new DateTime('2012-05-24 12:00:00'),
            ],
            [
                new DateTime('2012-04-16 12:00:00'),
                [
                    WeeklyInterval::MONDAY
                ],
                52,
                new DateTime('2013-04-14 12:00:00'),
            ],
        ];
    }

    /**
     * @return array
     */
    public function invalidWeeksProvider()
    {
        return [
            [true],
            [false],
            [[]],
            [new stdClass()],
            [-1],
            [0]
        ];
    }

    /**
     * @return array
     */
    public function invalidDaysProvider()
    {
        return [
            [99],
            [-1],
            [new stdClass()],
            [[]],
            ['test'],
            [true],
            [false],
            [[
                WeeklyInterval::MONDAY,
                WeeklyInterval::THURSDAY,
                WeeklyInterval::SUNDAY,
                99,
                'test'
            ]]
        ];
    }

    /**
     * @param array $days
     * @param int $weeks
     *
     * @return WeeklyInterval
     *
     */
    protected function generateWeeklyInterval($days = [0,1], $weeks = 1)
    {
        $class = self::INTERVAL_CLASS;

        return new $class($days, $weeks);
    }
}