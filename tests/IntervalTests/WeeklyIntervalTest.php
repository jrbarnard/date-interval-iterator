<?php
namespace JRBarnard\RecurrenceTests\IntervalTests;

use JRBarnard\Recurrence\Intervals\SetsDays;
use stdClass;
use DateTime;
use ReflectionClass;
use ReflectionObject;
use JRBarnard\RecurrenceTests\TestCase;
use JRBarnard\Recurrence\Intervals\WeeklyInterval;
use JRBarnard\Recurrence\Intervals\IntervalInterface;
use JRBarnard\Recurrence\Exceptions\BadMethodCallException;
use JRBarnard\Recurrence\Exceptions\InvalidArgumentException;

/**
 * Class WeeklyIntervalTest
 */
class WeeklyIntervalTest extends TestCase
{
    const INTERVAL_CLASS = WeeklyInterval::class;

    // Tests:
    // Interval constructor will call setDays and setWeeks - done
    // setWeeks accepts an int greater than 0, will store in weeks - done
    // if setWeeks not passed int greater than 0 will throw - done
    // can getWeeks using method, will get set - done
    // can getDays using method, will get set - done
    // Find next occurrence will work with relevant set days and weeks - done
    // Can run backwards - done
    // Can use magic setters and helper setters
    //  - everyTuesday, everyWednesday will overwrite - done
    //  - Will throw exception if non existent day - done
    //  - everyTuesday andEveryWednesday will append - done
    //  - andEvery will throw if invalid day - done
    //  - call andEveryWednesday twice will not add twice - done
    //  - ofEvery3rdWeek, ofEveryWeek, - done
    //  - ofEveryWeek will accept number of weeks - done
    //  - ofEvery{week}Week will throw if invalid - done
    //  - Test all together and getting correct occurrence
    // Uses SetsDays trait - done

    /** @test */
    public function uses_SetsDays_trait()
    {
        $this->assertArrayHasKey(SetsDays::class, class_uses(self::INTERVAL_CLASS));
    }

    /** @test */
    public function ofEveryWeek_magic_will_throw_if_invalid_weeks()
    {
        $interval = $this->generateWeeklyInterval([IntervalInterface::MONDAY], 2);

        $this->expectException(BadMethodCallException::class);
        $this->expectExceptionMessage('Call to undefined method {' . self::INTERVAL_CLASS . '}::{ofEveryhaljdaldkWeek}()');
        $interval->ofEveryhaljdaldkWeek();
    }

    /**
     * @dataProvider magicOfEveryWeekProvider
     * @test
     *
     * @param $methodName
     * @param $expectedWeeks
     */
    public function ofEveryWeek_will_accept_number_as_part_of_method_name($methodName, $expectedWeeks)
    {
        $interval = $this->generateWeeklyInterval([IntervalInterface::MONDAY], 2);

        $return = $interval->$methodName();

        $this->assertInstanceOf(self::INTERVAL_CLASS, $return);

        $this->assertEquals($expectedWeeks, $interval->getWeeks());
    }

    /** @test */
    public function ofEveryWeek_will_set_number_of_weeks_pass_through_to_set_weeks_defaults_to_one()
    {
        $interval = $this->generateWeeklyInterval([IntervalInterface::MONDAY], 2);
        $weeks = 4;

        $return = $interval->ofEveryWeek();

        $this->assertInstanceOf(self::INTERVAL_CLASS, $return);

        $this->assertEquals(1, $interval->getWeeks());

        $interval->ofEveryWeek($weeks);
        $this->assertEquals($weeks, $interval->getWeeks());
    }

    /** @test */
    public function andEvery_magic_method_will_append_onto_existing_days()
    {
        $interval = $this->generateWeeklyInterval();

        $interval->everyWednesday();

        $this->assertEquals([IntervalInterface::WEDNESDAY], $interval->getDays());

        $interval->andEveryFriday();
        $this->assertEquals([IntervalInterface::WEDNESDAY, IntervalInterface::FRIDAY], $interval->getDays());

        // Won't duplicate
        $interval->andEveryWednesday();
        $this->assertEquals([IntervalInterface::WEDNESDAY, IntervalInterface::FRIDAY], $interval->getDays());
    }

    /**
     * Test added to cover check within the magic caller that checks the day is a valid day after checking the constant
     * is defined, so we use another valid constant that is not a day to test.
     * @test */
    public function andEvery_magic_method_will_throw_if_pass_direction_constant()
    {
        $interval = $this->generateWeeklyInterval();

        $this->expectException(BadMethodCallException::class);
        $this->expectExceptionMessage('Call to undefined method {' . self::INTERVAL_CLASS . '}::{andEveryDirections}()');
        $interval->andEveryDirections();
    }

    /** @test */
    public function andEvery_magic_method_will_throw_if_invalid_day()
    {
        $interval = $this->generateWeeklyInterval();

        $this->expectException(BadMethodCallException::class);
        $this->expectExceptionMessage('Call to undefined method {' . self::INTERVAL_CLASS . '}::{andEveryNever}()');
        $interval->andEveryNever();
    }

    /**
     * @dataProvider magicAndEveryMethodProvider
     * @test
     *
     * @param $method
     * @param $expected
     */
    public function andEvery_magic_method_will_set_days($method, $expected)
    {
        $interval = $this->generateWeeklyInterval();

        // Remove days to start with
        $class = new ReflectionObject($interval);
        $property = $class->getProperty('days');
        $property->setAccessible(true);
        $property->setValue($interval, []);

        $return = $interval->$method();

        $this->assertInstanceOf(self::INTERVAL_CLASS, $return);

        $this->assertEquals([$expected], $interval->getDays());
    }

    /** @test */
    public function calling_invalid_magic_setter_name_will_throw_exception()
    {
        $interval = $this->generateWeeklyInterval();

        $this->expectException(BadMethodCallException::class);
        $this->expectExceptionMessage('Call to undefined method {' . self::INTERVAL_CLASS . '}::{everyNever}()');
        $interval->everyNever();
    }

    /** @test */
    public function calling_magic_every_setter_twice_will_override()
    {
        $interval = $this->generateWeeklyInterval();

        $interval->everyWednesday();

        $this->assertEquals([IntervalInterface::WEDNESDAY], $interval->getDays());

        $interval->everySunday();

        $this->assertEquals([IntervalInterface::SUNDAY], $interval->getDays());
    }

    /**
     * @dataProvider magicWeekSetterProvider
     * @test
     *
     * @param $methodName
     * @param $result
     */
    public function magic_setter_every_will_use_name_in_method_to_set_day($methodName, $result)
    {
        $interval = $this->generateWeeklyInterval();

        $return = $interval->$methodName();

        $this->assertInstanceOf(self::INTERVAL_CLASS, $return);

        $this->assertEquals([$result], $interval->getDays());
    }

    /** @test */
    public function interval_construtor_will_call_set_weeks_and_set_days()
    {
        $className = self::INTERVAL_CLASS;
        $weeks = 10;
        $days = [
            IntervalInterface::TUESDAY,
            IntervalInterface::WEDNESDAY
        ];

        // Get mock, without the constructor being called
        $mock = $this->getMockBuilder($className)
            ->disableOriginalConstructor()
            ->setMethods([
                'setDays',
                'setWeeks'
            ])
            ->getMockForAbstractClass();

        // set expectations for constructor calls
        $mock->expects($this->once())
            ->method('setDays')
            ->with($days);
        $mock->expects($this->once())
            ->method('setWeeks')
            ->with($weeks);

        // now call the constructor
        $reflectedClass = new ReflectionClass($className);
        $constructor = $reflectedClass->getConstructor();
        $constructor->invoke($mock, $days, $weeks);
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

    /**
     * HELPERS & PROVIDERS
     */

    /**
     * @return array
     */
    public function magicOfEveryWeekProvider()
    {
        return [
            [
                'ofEvery1stWeek',
                1
            ],
            [
                'ofEvery2ndWeek',
                2
            ],
            [
                'ofEvery3rdWeek',
                3
            ],
            [
                'ofEvery4thWeek',
                4
            ],
            [
                'ofEvery10thWeek',
                10
            ],
            [
                'ofEvery21stWeek',
                21
            ],
        ];
    }

    /**
     * @return array
     */
    public function magicAndEveryMethodProvider()
    {
        return [
            [
                'andEveryMonday',
                IntervalInterface::MONDAY
            ],
            [
                'andEveryTuesday',
                IntervalInterface::TUESDAY
            ],
            [
                'andEveryWednesday',
                IntervalInterface::WEDNESDAY
            ],
            [
                'andEveryThursday',
                IntervalInterface::THURSDAY
            ],
            [
                'andEveryFriday',
                IntervalInterface::FRIDAY
            ],
            [
                'andEverySaturday',
                IntervalInterface::SATURDAY
            ],
            [
                'andEverySunday',
                IntervalInterface::SUNDAY
            ]
        ];
    }

    /**
     * @return array
     */
    public function magicWeekSetterProvider()
    {
        return [
            [
                'everyMonday',
                IntervalInterface::MONDAY
            ],
            [
                'everyTuesday',
                IntervalInterface::TUESDAY
            ],
            [
                'everyWednesday',
                IntervalInterface::WEDNESDAY
            ],
            [
                'everyThursday',
                IntervalInterface::THURSDAY
            ],
            [
                'everyFriday',
                IntervalInterface::FRIDAY
            ],
            [
                'everySaturday',
                IntervalInterface::SATURDAY
            ],
            [
                'everySunday',
                IntervalInterface::SUNDAY
            ]
        ];
    }

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