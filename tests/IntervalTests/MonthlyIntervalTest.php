<?php
namespace JRBarnard\RecurrenceTests\IntervalTests;

use DateTime;
use ReflectionClass;
use JRBarnard\RecurrenceTests\TestCase;
use JRBarnard\Recurrence\Intervals\SetsDays;
use JRBarnard\Recurrence\Intervals\MonthlyInterval;
use JRBarnard\Recurrence\Intervals\IntervalInterface;
use JRBarnard\Recurrence\Exceptions\BadMethodCallException;
use JRBarnard\Recurrence\Exceptions\InvalidArgumentException;

/**
 * Class MonthlyIntervalTest
 */
class MonthlyIntervalTest extends TestCase
{
    /**
     * Tests:
     *  - constructor
     *      - optional should accept one of frequency constants on MonthlyInterval - done
     *      - optional should accept array of days of week as second param - done
     *      - optional should accept number of months as third param - done
     *      - will call setDays, setFrequency and setMonths - done
     *  - uses SetsDays trait - done
     *  - setFrequency
     *      - will accept any of the frequency constants on MonthlyInterval and set frequency prop - done
     *      - will throw if invalid - done
     *      - will return self - done
     *  - getFrequency
     *      - will return set frequency - done
     *  - setMonths
     *      - will set months prop - done
     *      - will return self - done
     *      - will throw if not int or 1->12 - done
     *  - getMonths
     *      - will get months set - done
     *      - will default to 1 - done
     *  - findNextOccurrence
     *      - will find next occurrence based on set criteria (using provider with multiple data points) - done
     *      - will work backwards - done
     *      - if attempt to run when has days but no frequency set throw - done
     *  - with iterator
     *      - if forces to end of month, next month which has the valid days will use them - done
     *  - every fluent setter
     *      - will accept frequency and days as params and call setFrequency and setDays - done
     *      - will return self - done
     *  - ofEveryMonth fluent setter
     *      - will accept int and pass to setMonth - done
     *      - will return self - done
     *  - every{Frequency} fluent setter
     *      - will set frequency based on frequency used (e.g everyLast) - done
     *      - will throw if invalid - done
     *      - will call setFrequency and pass through frequency - done
     *      - will return self - done
     *  - {day constant} fluent setter
     *      - will throw if invalid day - done
     *      - will pass through to setDays - done
     *      - will return self - done
     *  - and{day constant} fluent setter
     *      - will throw if invalid day constant - done
     *      - will get days then add passed day and set days to setDays - done
     *      - if same day already exists will only set unique - done
     *      - will return self - done
     *  - ofEvery{1st->12th & Other}Month fluent setter
     *      - will throw if invalid - done
     *      - will pass parsed regularity of months to setMonths - done
     *      - will return self - done
     */

    /**
     * @dataProvider ofEveryNMonthValidProvider
     * @test
     *
     * @param $method
     * @param $expected
     */
    public function ofEveryNMonth_fluent_setter_will_pass_parsed_months_to_setMonths_and_return_self($method, $expected)
    {
        $interval = new MonthlyInterval();
        $result = $interval->{$method}();
        $this->assertSame($result, $interval);
        $this->assertSame($expected, $interval->getMonths());
    }

    /**
     * @dataProvider ofEveryNMonthInvalidMethodsProvider
     * @test
     *
     * @param $invalidMethod
     */
    public function ofEveryNMonth_fluent_setter_will_throw_if_invalid($invalidMethod)
    {
        $interval = new MonthlyInterval();
        $this->expectException(BadMethodCallException::class);
        $interval->{$invalidMethod}();
    }

    /** @test */
    public function and_day_constant_fluent_setter_will_set_days_with_uniqued_days()
    {
        $interval = new MonthlyInterval();
        $interval->setDays($days = [
            IntervalInterface::WEDNESDAY,
            IntervalInterface::THURSDAY
        ]);
        $this->assertEquals($days, $interval->getDays());

        $interval->andThursday();

        $this->assertSame($days, $interval->getDays());
    }

    /** @test */
    public function and_day_constant_fluent_setter_will_call_set_days_and_add_and_return_self()
    {
        $map = [
            IntervalInterface::SUNDAY => 'andSunday',
            IntervalInterface::MONDAY => 'andMonday',
            IntervalInterface::TUESDAY => 'andTuesday',
            IntervalInterface::WEDNESDAY => 'andWednesday',
            IntervalInterface::THURSDAY => 'andThursday',
            IntervalInterface::FRIDAY => 'andFriday',
            IntervalInterface::SATURDAY => 'andSaturday',
        ];

        $setUpMock = function ($day) {
            $mockInterval = $this->getMockBuilder(MonthlyInterval::class)
                ->disableOriginalConstructor()
                ->setMethods([
                    'setDays',
                ])
                ->getMock();

            $mockInterval->expects($this->once())
                ->method('setDays')
                ->with([$day])
                ->willReturnSelf();

            return $mockInterval;
        };

        foreach ($map as $day => $method) {
            $mockInterval = $setUpMock($day);
            $this->assertSame($mockInterval, $mockInterval->{$method}($day));
        }

        // Test with adding to existing
        foreach ($map as $day => $method) {
            // Get other days to choose from
            $otherDays = array_rand(array_filter($map, function ($otherDay) use ($day) {
                return $otherDay !== $day;
            }, ARRAY_FILTER_USE_KEY), 2);
            $this->assertCount(2, $otherDays);
            foreach ($otherDays as $otherDay) {
                $this->assertNotSame($day, $otherDay);
            }

            $interval = new MonthlyInterval();
            $interval->setDays($otherDays);
            $this->assertEquals($otherDays, $interval->getDays());

            $this->assertSame($interval, $interval->{$method}($day));
            $daysResult = $interval->getDays();
            $expectedDaysResult = array_merge($otherDays, [$day]);
            sort($daysResult);
            sort($expectedDaysResult);
            $this->assertSame($expectedDaysResult, $daysResult);
        }
    }

    /** @test */
    public function and_day_constant_fluent_setter_will_throw_if_invalid_day()
    {
        $this->expectException(BadMethodCallException::class);
        (new MonthlyInterval())->andInvalidDayOfTheWeek(IntervalInterface::TUESDAY);
    }

    /** @test */
    public function day_constant_fluent_setter_will_call_set_days_and_return_self()
    {
        $map = [
            IntervalInterface::SUNDAY => 'sunday',
            IntervalInterface::MONDAY => 'monday',
            IntervalInterface::TUESDAY => 'tuesday',
            IntervalInterface::WEDNESDAY => 'wednesday',
            IntervalInterface::THURSDAY => 'thursday',
            IntervalInterface::FRIDAY => 'friday',
            IntervalInterface::SATURDAY => 'saturday',
        ];

        $setUpMock = function ($day) {
            $mockInterval = $this->getMockBuilder(MonthlyInterval::class)
                ->disableOriginalConstructor()
                ->setMethods([
                    'setDays',
                ])
                ->getMock();

            $mockInterval->expects($this->once())
                ->method('setDays')
                ->with([$day])
                ->willReturnSelf();

            return $mockInterval;
        };

        foreach ($map as $day => $method) {
            $mockInterval = $setUpMock($day);
            $this->assertSame($mockInterval, $mockInterval->{$method}($day));
        }
    }

    /** @test */
    public function day_constant_fluent_setter_will_throw_if_invalid()
    {
        $this->expectException(BadMethodCallException::class);
        (new MonthlyInterval())->invalidDayOfTheWeek(IntervalInterface::TUESDAY);
    }

    /** @test */
    public function everyFrequency_fluent_setter_will_call_setFrequency_with_relevant_frequency()
    {
        $map = [
            MonthlyInterval::FREQUENCY_FIRST => 'everyFirst',
            MonthlyInterval::FREQUENCY_SECOND => 'everySecond',
            MonthlyInterval::FREQUENCY_THIRD => 'everyThird',
            MonthlyInterval::FREQUENCY_FOURTH => 'everyFourth',
            MonthlyInterval::FREQUENCY_FIFTH => 'everyFifth',
            MonthlyInterval::FREQUENCY_LAST => 'everyLast'
        ];

        $setUpMock = function ($frequency) {
            $mockInterval = $this->getMockBuilder(MonthlyInterval::class)
                ->disableOriginalConstructor()
                ->setMethods([
                    'setFrequency',
                ])
                ->getMock();

            $mockInterval->expects($this->once())
                ->method('setFrequency')
                ->with($frequency)
                ->willReturnSelf();

            return $mockInterval;
        };

        foreach ($map as $frequency => $method) {
            $mockInterval = $setUpMock($frequency);
            $this->assertSame($mockInterval, $mockInterval->{$method}($frequency));
        }
    }

    /** @test */
    public function everyFrequency_fluent_setter_will_throw_if_invalid_frequency()
    {
        $this->expectException(BadMethodCallException::class);
        (new MonthlyInterval())->everyInvalidFrequency(MonthlyInterval::FREQUENCY_THIRD);
    }

    /** @test */
    public function ofEveryMonth_will_accept_month_and_pass_to_set_month()
    {
        $mockInterval = $this->getMockBuilder(MonthlyInterval::class)
            ->disableOriginalConstructor()
            ->setMethods([
                'setMonths',
            ])
            ->getMock();

        $month = 7;

        $mockInterval->expects($this->once())
            ->method('setMonths')
            ->with($month)
            ->willReturnSelf();

        $this->assertSame($mockInterval, $mockInterval->ofEveryMonth($month));
    }

    /** @test */
    public function every_fluent_setter_will_accept_frequency_and_days_and_call_setFrequency_and_set_days()
    {
        $mockInterval = $this->getMockBuilder(MonthlyInterval::class)
            ->disableOriginalConstructor()
            ->setMethods([
                'setDays',
                'setFrequency',
            ])
            ->getMock();

        $frequency = MonthlyInterval::FREQUENCY_SECOND;
        $days = [
            IntervalInterface::MONDAY,
            IntervalInterface::SUNDAY
        ];

        $mockInterval->expects($this->once())
            ->method('setDays')
            ->with($days)
            ->willReturnSelf();
        $mockInterval->expects($this->once())
            ->method('setFrequency')
            ->with($frequency)
            ->willReturnSelf();

        $this->assertSame($mockInterval, $mockInterval->every($frequency, $days));
    }

    /** @test */
    public function findNextOccurrence_if_attempt_to_run_when_has_days_but_no_frequency_set_throw()
    {
        $interval = new MonthlyInterval();

        $interval->setFrequency(null);
        $interval->setDays([IntervalInterface::MONDAY]);
        $interval->setMonths(1);

        $this->expectException(InvalidArgumentException::class);
        $interval->findNextOccurrence(new DateTime());
    }

    /** @test */
    public function with_iterator_if_goes_to_month_max_days_next_iteration_will_have_days_as_expected()
    {
        $start = new DateTime('2012-01-30');
        $interval = new MonthlyInterval();
        $iterator = $this->generateIterator($start, $interval, 3);

        $expected = [
            '2012-02-29',
            '2012-03-30',
            '2012-04-30'
        ];
        $this->assertSame(count($expected), $iterator->count());
        foreach ($iterator as $index => $occurrence) {
            $this->assertSame($expected[$index], $occurrence->format('Y-m-d'));
        }
    }

    /**
     * @dataProvider findNextOccurrenceBackwardsProvider
     * @test
     *
     * @param $start
     * @param $frequency
     * @param $days
     * @param $months
     * @param $expected
     *
     * @throws \Exception
     */
    public function findNextOccurrence_will_get_next_occurrence_based_on_criteria_set_backwards(
        $start,
        $frequency,
        $days,
        $months,
        $expected
    ) {
        $interval = new MonthlyInterval();

        $interval->setFrequency($frequency);
        $interval->setDays($days);
        $interval->setMonths($months);

        $next = $interval->findNextOccurrence($start, IntervalInterface::BACKWARDS);

        $this->assertSame(
            $expected->getTimestamp(),
            $next->getTimestamp(),
            'Failed asserting that next occurrence (' . $next->format('Y-m-d H:i:s') . '), is the ' .
            'same as the expected next occurrence (' . $expected->format('Y-m-d H:i:s') . ')'
        );
    }

    /**
     * @dataProvider findNextOccurrenceProvider
     * @test
     *
     * @param $start
     * @param $frequency
     * @param $days
     * @param $months
     * @param $expected
     *
     * @throws \Exception
     */
    public function findNextOccurrence_will_get_next_occurrence_based_on_criteria_set(
        $start,
        $frequency,
        $days,
        $months,
        $expected
    ) {
        $interval = new MonthlyInterval();

        $interval->setFrequency($frequency);
        $interval->setDays($days);
        $interval->setMonths($months);

        $next = $interval->findNextOccurrence($start);

        $this->assertSame(
            $expected->getTimestamp(),
            $next->getTimestamp(),
            'Failed asserting that next occurrence (' . $next->format('Y-m-d H:i:s') . '), is the ' .
            'same as the expected next occurrence (' . $expected->format('Y-m-d H:i:s') . ')'
        );
    }

    /** @test */
    public function getMonths_will_return_set_months()
    {
        $interval = new MonthlyInterval();
        $class = new \ReflectionObject($interval);
        $property = $class->getProperty('months');
        $property->setAccessible(true);

        // Test default
        $this->assertSame(1, $interval->getMonths());
        $property->setValue($interval, 4);
        $this->assertSame(4, $interval->getMonths());
    }

    /** @test */
    public function setMonths_will_set_if_valid()
    {
        // 1 -> 12
        for ($i = 1; $i < 13; $i++) {
            $interval = new MonthlyInterval();
            $this->assertSame($interval, $interval->setMonths($i));
            $this->assertSame($i, $interval->getMonths());
        }
    }

    /**
     * @dataProvider invalidMonthsProvider
     * @test
     *
     * @param $invalidMonths
     */
    public function setMonths_will_throw_if_invalid($invalidMonths)
    {
        $this->expectException(InvalidArgumentException::class);
        $interval = new MonthlyInterval();
        $interval->setMonths($invalidMonths);
    }

    /** @test */
    public function getFrequency_will_return_set_frequency()
    {
        $interval = new MonthlyInterval();
        $class = new \ReflectionObject($interval);
        $property = $class->getProperty('frequency');
        $property->setAccessible(true);

        $this->assertNull($interval->getFrequency());
        $property->setValue($interval, MonthlyInterval::FREQUENCY_LAST);
        $this->assertSame(MonthlyInterval::FREQUENCY_LAST, $interval->getFrequency());
    }

    /** @test */
    public function setFrequency_will_accept_any_valid_frequency_and_return_self()
    {
        foreach (MonthlyInterval::FREQUENCIES as $frequency) {
            $interval = new MonthlyInterval();
            $this->assertSame($interval, $interval->setFrequency($frequency));
            $this->assertSame($frequency, $interval->getFrequency());
        }

        // Test null after it being set
        $interval = new MonthlyInterval();
        $interval->setFrequency(MonthlyInterval::FREQUENCY_LAST);
        $this->assertSame(MonthlyInterval::FREQUENCY_LAST, $interval->getFrequency());
        $this->assertSame($interval, $interval->setFrequency(null));
        $this->assertNull($interval->getFrequency());
    }

    /**
     * @dataProvider invalidFrequencyProvider
     * @test
     *
     * @param $invalidFrequency
     */
    public function setFrequency_will_throw_if_invalid_frequency($invalidFrequency)
    {
        $this->expectException(InvalidArgumentException::class);
        $interval = new MonthlyInterval();
        $interval->setFrequency($invalidFrequency);
    }

    /** @test */
    public function uses_SetsDays_trait()
    {
        $this->assertArrayHasKey(SetsDays::class, class_uses(MonthlyInterval::class));
    }

    /** @test */
    public function constructor_will_call_setDays_setMonths_and_setFrequency()
    {
        $className = MonthlyInterval::class;
        $months = 10;
        $days = [
            IntervalInterface::TUESDAY,
            IntervalInterface::WEDNESDAY
        ];
        $frequency = MonthlyInterval::FREQUENCY_FIRST;

        // Get mock, without the constructor being called
        $mock = $this->getMockBuilder($className)
            ->disableOriginalConstructor()
            ->setMethods([
                'setDays',
                'setMonths',
                'setFrequency',
            ])
            ->getMockForAbstractClass();

        // set expectations for constructor calls
        $mock->expects($this->once())
            ->method('setDays')
            ->with($days);
        $mock->expects($this->once())
            ->method('setMonths')
            ->with($months);
        $mock->expects($this->once())
            ->method('setFrequency')
            ->with($frequency);

        // now call the constructor
        $reflectedClass = new ReflectionClass($className);
        $constructor = $reflectedClass->getConstructor();
        $constructor->invoke($mock, $frequency, $days, $months);
    }

    /** @test */
    public function constructor_accepts_optional_months_as_third_param()
    {
        $interval = new MonthlyInterval(MonthlyInterval::FREQUENCY_FIRST, [], 3);
        $this->assertSame(3, $interval->getMonths());

        // Defaults to null
        $interval = new MonthlyInterval(MonthlyInterval::FREQUENCY_FIRST);
        $this->assertSame(1, $interval->getMonths());
    }

    /** @test */
    public function constructor_accepts_optional_months_as_third_param_throw_if_invalid()
    {
        $this->expectException(InvalidArgumentException::class);
        new MonthlyInterval(MonthlyInterval::FREQUENCY_FIRST, [], 'invalid');
    }

    /** @test */
    public function constructor_accepts_optional_days_as_second_param()
    {
        $interval = new MonthlyInterval(MonthlyInterval::FREQUENCY_FIRST, $daysSet = [
            IntervalInterface::MONDAY,
            IntervalInterface::TUESDAY
        ]);
        $this->assertSame($daysSet, $interval->getDays());

        // Defaults to null
        $interval = new MonthlyInterval(MonthlyInterval::FREQUENCY_FIRST);
        $this->assertSame([], $interval->getDays());
    }

    /** @test */
    public function constructor_accepts_optional_days_as_second_param_throw_if_invalid()
    {
        $this->expectException(InvalidArgumentException::class);
        new MonthlyInterval(MonthlyInterval::FREQUENCY_FIRST, 'invalid');
    }

    /** @test */
    public function constructor_accepts_optional_frequency_constant_as_first_param()
    {
        $interval = new MonthlyInterval(MonthlyInterval::FREQUENCY_FIRST);
        $this->assertInstanceOf(MonthlyInterval::class, $interval);
        $this->assertSame(MonthlyInterval::FREQUENCY_FIRST, $interval->getFrequency());

        // Defaults to null
        $interval = new MonthlyInterval();
        $this->assertInstanceOf(MonthlyInterval::class, $interval);
        $this->assertNull($interval->getFrequency());
    }

    /** @test */
    public function constructor_accepts_optional_frequency_constant_as_first_param_throw_if_invalid()
    {
        $this->expectException(InvalidArgumentException::class);
        new MonthlyInterval('invalid');
    }

    /**
     * HELPERS & GENERATORS
     */

    /**
     * @return array
     */
    public function invalidFrequencyProvider()
    {
        return [
            [new \stdClass()],
            ['woah!!!'],
            [[]],
            [-11282],
            ['207824'],
            [234264]
        ];
    }

    /**
     * @return array
     */
    public function invalidMonthsProvider()
    {
        return [
            ['woah!!'],
            [[]],
            [new \stdClass()],
            [-1],
            [0],
            [-12471],
            [13],
            ['247752'],
            ['1'],
        ];
    }

    /**
     * @return array
     * @throws \Exception
     */
    public function findNextOccurrenceProvider()
    {
        // DateTime start, frequency, days, months, DateTime expected, optional bool startsThisMonth
        return [
            // Start with just default month tests
            [
                new DateTime('2012-04-19 12:00:00'),
                null,
                [],
                1,
                new DateTime('2012-05-19 12:00:00'),
            ],
            [
                new DateTime('2012-04-19 12:00:00'),
                null,
                [],
                2,
                new DateTime('2012-06-19 12:00:00'),
            ],
            [
                new DateTime('2012-04-19 12:00:00'),
                null,
                [],
                3,
                new DateTime('2012-07-19 12:00:00'),
            ],
            [
                new DateTime('2012-04-19 12:00:00'),
                null,
                [],
                4,
                new DateTime('2012-08-19 12:00:00'),
            ],
            [
                new DateTime('2012-04-19 12:00:00'),
                null,
                [],
                5,
                new DateTime('2012-09-19 12:00:00'),
            ],
            [
                new DateTime('2012-04-19 12:00:00'),
                null,
                [],
                6,
                new DateTime('2012-10-19 12:00:00'),
            ],
            [
                new DateTime('2012-04-19 12:00:00'),
                null,
                [],
                7,
                new DateTime('2012-11-19 12:00:00'),
            ],
            [
                new DateTime('2012-04-19 12:00:00'),
                null,
                [],
                8,
                new DateTime('2012-12-19 12:00:00'),
            ],
            [
                new DateTime('2012-04-19 12:00:00'),
                null,
                [],
                9,
                new DateTime('2013-01-19 12:00:00'),
            ],
            [
                new DateTime('2012-04-19 12:00:00'),
                null,
                [],
                10,
                new DateTime('2013-02-19 12:00:00'),
            ],
            [
                new DateTime('2012-04-19 12:00:00'),
                null,
                [],
                11,
                new DateTime('2013-03-19 12:00:00'),
            ],
            [
                new DateTime('2012-04-19 12:00:00'),
                null,
                [],
                12,
                new DateTime('2013-04-19 12:00:00'),
            ],
            // Try a february month test
            [
                new DateTime('2012-01-30 12:00:00'),
                null,
                [],
                1,
                new DateTime('2012-02-29 12:00:00'), // leap year
            ],
            [
                new DateTime('2013-01-30 12:00:00'),
                null,
                [],
                1,
                new DateTime('2013-02-28 12:00:00'), // non leap
            ],
            // Test 2 months
            [
                new DateTime('2011-12-30 12:00:00'),
                null,
                [],
                2,
                new DateTime('2012-02-29 12:00:00'), // leap year
            ],
            [
                new DateTime('2012-12-30 12:00:00'),
                null,
                [],
                2,
                new DateTime('2013-02-28 12:00:00'), // non leap
            ],
            // Add in a frequency and days
            [
                new DateTime('2012-04-19 12:00:00'),
                MonthlyInterval::FREQUENCY_FIRST,
                [IntervalInterface::TUESDAY],
                1,
                new DateTime('2012-05-01 12:00:00'),
            ],
            [
                new DateTime('2012-05-01 12:00:00'),
                MonthlyInterval::FREQUENCY_FIRST,
                [IntervalInterface::TUESDAY],
                1,
                new DateTime('2012-06-05 12:00:00'),
            ],
            // Multiple days
            [
                new DateTime('2012-06-06 12:00:00'),
                MonthlyInterval::FREQUENCY_FIRST,
                [IntervalInterface::TUESDAY, IntervalInterface::THURSDAY],
                1,
                new DateTime('2012-06-07 12:00:00'),
            ],
            [
                new DateTime('2012-06-07 12:00:00'),
                MonthlyInterval::FREQUENCY_FIRST,
                [IntervalInterface::TUESDAY, IntervalInterface::THURSDAY],
                1,
                new DateTime('2012-07-03 12:00:00'),
            ],
            [
                new DateTime('2012-07-02 12:00:00'),
                MonthlyInterval::FREQUENCY_FIRST,
                [IntervalInterface::TUESDAY, IntervalInterface::THURSDAY],
                1,
                new DateTime('2012-07-03 12:00:00'),
            ],
            [
                new DateTime('2012-06-06 12:00:00'),
                MonthlyInterval::FREQUENCY_FIRST,
                [IntervalInterface::TUESDAY, IntervalInterface::THURSDAY],
                1,
                new DateTime('2012-06-07 12:00:00')
            ],
            [
                new DateTime('2012-06-07 12:00:00'),
                MonthlyInterval::FREQUENCY_FIRST,
                [IntervalInterface::TUESDAY, IntervalInterface::THURSDAY],
                1,
                new DateTime('2012-07-03 12:00:00')
            ],
            [
                new DateTime('2012-07-02 12:00:00'),
                MonthlyInterval::FREQUENCY_FIRST,
                [IntervalInterface::TUESDAY, IntervalInterface::THURSDAY],
                1,
                new DateTime('2012-07-03 12:00:00')
            ],
            [
                new DateTime('2012-07-05 12:00:00'),
                MonthlyInterval::FREQUENCY_FIRST,
                [IntervalInterface::TUESDAY, IntervalInterface::THURSDAY],
                1,
                new DateTime('2012-08-02 12:00:00')
            ],
            // Second frequency
            [
                new DateTime('2012-04-19 12:00:00'),
                MonthlyInterval::FREQUENCY_SECOND,
                [IntervalInterface::TUESDAY],
                1,
                new DateTime('2012-05-08 12:00:00'),
            ],
            [
                new DateTime('2012-04-09 12:00:00'),
                MonthlyInterval::FREQUENCY_SECOND,
                [IntervalInterface::TUESDAY],
                1,
                new DateTime('2012-04-10 12:00:00')
            ],
            // Third frequency
            [
                new DateTime('2012-04-19 12:00:00'),
                MonthlyInterval::FREQUENCY_THIRD,
                [IntervalInterface::TUESDAY],
                1,
                new DateTime('2012-05-15 12:00:00'),
            ],
            [
                new DateTime('2012-04-15 12:00:00'),
                MonthlyInterval::FREQUENCY_THIRD,
                [IntervalInterface::TUESDAY],
                1,
                new DateTime('2012-04-17 12:00:00')
            ],
            // Fourth frequency
            [
                new DateTime('2012-04-19 12:00:00'),
                MonthlyInterval::FREQUENCY_FOURTH,
                [IntervalInterface::WEDNESDAY],
                1,
                new DateTime('2012-04-25 12:00:00'),
            ],
            [
                new DateTime('2012-04-15 12:00:00'),
                MonthlyInterval::FREQUENCY_FOURTH,
                [IntervalInterface::TUESDAY],
                1,
                new DateTime('2012-04-24 12:00:00')
            ],
            // Fifth frequency
            [
                new DateTime('2012-04-19 12:00:00'),
                MonthlyInterval::FREQUENCY_FIFTH,
                [IntervalInterface::MONDAY],
                1,
                new DateTime('2012-04-30 12:00:00'),
            ],
            [
                new DateTime('2012-05-31 12:00:00'),
                MonthlyInterval::FREQUENCY_FIFTH,
                [IntervalInterface::WEDNESDAY],
                1,
                new DateTime('2012-08-29 12:00:00'),
            ],
            // Last frequency
            [
                new DateTime('2012-04-24 12:00:00'),
                MonthlyInterval::FREQUENCY_LAST,
                [IntervalInterface::TUESDAY],
                1,
                new DateTime('2012-05-29 12:00:00'),
            ],
            [
                new DateTime('2012-04-15 12:00:00'),
                MonthlyInterval::FREQUENCY_LAST,
                [IntervalInterface::MONDAY],
                1,
                new DateTime('2012-04-30 12:00:00')
            ],
            // Multiple days, different frequencies
            [
                new DateTime('2012-04-19 12:00:00'),
                MonthlyInterval::FREQUENCY_LAST,
                [IntervalInterface::TUESDAY, IntervalInterface::SATURDAY],
                1,
                new DateTime('2012-04-24 12:00:00'),
            ],
            [
                new DateTime('2012-04-25 12:00:00'),
                MonthlyInterval::FREQUENCY_LAST,
                [IntervalInterface::TUESDAY, IntervalInterface::MONDAY],
                1,
                new DateTime('2012-04-30 12:00:00'),
            ],
            [
                new DateTime('2012-04-30 12:00:00'),
                MonthlyInterval::FREQUENCY_LAST,
                [IntervalInterface::TUESDAY, IntervalInterface::MONDAY],
                1,
                new DateTime('2012-05-28 12:00:00'),
            ],
            // Multiple days, multiple months
            [
                new DateTime('2013-06-06 12:00:00'),
                MonthlyInterval::FREQUENCY_FIRST,
                [IntervalInterface::SATURDAY, IntervalInterface::SUNDAY],
                2,
                new DateTime('2013-08-03 12:00:00'),
            ],
            [
                new DateTime('2013-06-07 12:00:00'),
                MonthlyInterval::FREQUENCY_SECOND,
                [IntervalInterface::SATURDAY, IntervalInterface::THURSDAY],
                3,
                new DateTime('2013-06-08 12:00:00'),
            ],
            [
                new DateTime('2013-06-13 12:00:00'),
                MonthlyInterval::FREQUENCY_SECOND,
                [IntervalInterface::SATURDAY, IntervalInterface::THURSDAY],
                3,
                new DateTime('2013-09-12 12:00:00'),
            ],
            [
                new DateTime('2013-06-13 12:00:00'),
                MonthlyInterval::FREQUENCY_SECOND,
                [IntervalInterface::SUNDAY, IntervalInterface::THURSDAY],
                3,
                new DateTime('2013-09-08 12:00:00'),
            ],
            [
                new DateTime('2013-06-19 12:00:00'),
                MonthlyInterval::FREQUENCY_THIRD,
                [IntervalInterface::WEDNESDAY],
                11,
                new DateTime('2014-05-21 12:00:00'),
            ],
            [
                new DateTime('2013-06-19 12:00:00'),
                MonthlyInterval::FREQUENCY_FIFTH,
                [IntervalInterface::WEDNESDAY],
                6,
                new DateTime('2014-12-31 12:00:00'),
            ],
        ];
    }

    /**
     * @return array
     * @throws \Exception
     */
    public function findNextOccurrenceBackwardsProvider()
    {
        // DateTime start, frequency, days, months, DateTime expected
        return [
            // Start with just default month tests
            [
                new DateTime('2012-05-19 12:00:00'),
                null,
                [],
                1,
                new DateTime('2012-04-19 12:00:00'),
            ],
            [
                new DateTime('2012-06-19 12:00:00'),
                null,
                [],
                2,
                new DateTime('2012-04-19 12:00:00'),
            ],
            [
                new DateTime('2012-07-19 12:00:00'),
                null,
                [],
                3,
                new DateTime('2012-04-19 12:00:00'),
            ],
            [
                new DateTime('2012-08-19 12:00:00'),
                null,
                [],
                4,
                new DateTime('2012-04-19 12:00:00'),
            ],
            [
                new DateTime('2012-09-19 12:00:00'),
                null,
                [],
                5,
                new DateTime('2012-04-19 12:00:00'),
            ],
            [
                new DateTime('2012-10-19 12:00:00'),
                null,
                [],
                6,
                new DateTime('2012-04-19 12:00:00'),
            ],
            [
                new DateTime('2012-11-19 12:00:00'),
                null,
                [],
                7,
                new DateTime('2012-04-19 12:00:00'),
            ],
            [
                new DateTime('2012-12-19 12:00:00'),
                null,
                [],
                8,
                new DateTime('2012-04-19 12:00:00'),
            ],
            [
                new DateTime('2013-01-19 12:00:00'),
                null,
                [],
                9,
                new DateTime('2012-04-19 12:00:00'),
            ],
            [
                new DateTime('2013-02-19 12:00:00'),
                null,
                [],
                10,
                new DateTime('2012-04-19 12:00:00'),
            ],
            [
                new DateTime('2013-03-19 12:00:00'),
                null,
                [],
                11,
                new DateTime('2012-04-19 12:00:00'),
            ],
            [
                new DateTime('2013-04-19 12:00:00'),
                null,
                [],
                12,
                new DateTime('2012-04-19 12:00:00'),
            ],
            // Try a february month test
            [
                new DateTime('2012-03-30 12:00:00'), // leap year
                null,
                [],
                1,
                new DateTime('2012-02-29 12:00:00'),
            ],
            [
                new DateTime('2013-03-30 12:00:00'), // non leap
                null,
                [],
                1,
                new DateTime('2013-02-28 12:00:00'),
            ],
            // Test 2 months
            [
                new DateTime('2012-04-30 12:00:00'), // leap year
                null,
                [],
                2,
                new DateTime('2012-02-29 12:00:00'),
            ],
            [
                new DateTime('2013-04-30 12:00:00'), // non leap
                null,
                [],
                2,
                new DateTime('2013-02-28 12:00:00'),
            ],
            // Add in a frequency and days
            [
                new DateTime('2012-04-19 12:00:00'),
                MonthlyInterval::FREQUENCY_FIRST,
                [IntervalInterface::TUESDAY],
                1,
                new DateTime('2012-04-03 12:00:00'),
            ],
            [
                new DateTime('2012-05-01 12:00:00'),
                MonthlyInterval::FREQUENCY_FIRST,
                [IntervalInterface::TUESDAY],
                1,
                new DateTime('2012-04-03 12:00:00'),
            ],
            // Multiple days
            [
                new DateTime('2012-06-06 12:00:00'),
                MonthlyInterval::FREQUENCY_FIRST,
                [IntervalInterface::TUESDAY, IntervalInterface::THURSDAY],
                1,
                new DateTime('2012-06-05 12:00:00'),
            ],
            [
                new DateTime('2012-06-02 12:00:00'),
                MonthlyInterval::FREQUENCY_FIRST,
                [IntervalInterface::TUESDAY, IntervalInterface::THURSDAY],
                1,
                new DateTime('2012-05-03 12:00:00'),
            ],
            [
                new DateTime('2012-07-02 12:00:00'),
                MonthlyInterval::FREQUENCY_FIRST,
                [IntervalInterface::TUESDAY, IntervalInterface::THURSDAY],
                1,
                new DateTime('2012-06-07 12:00:00'),
            ],
            [
                new DateTime('2012-06-06 12:00:00'),
                MonthlyInterval::FREQUENCY_FIRST,
                [IntervalInterface::TUESDAY, IntervalInterface::THURSDAY],
                1,
                new DateTime('2012-06-05 12:00:00')
            ],
            [
                new DateTime('2012-06-04 12:00:00'),
                MonthlyInterval::FREQUENCY_FIRST,
                [IntervalInterface::TUESDAY, IntervalInterface::THURSDAY],
                1,
                new DateTime('2012-05-03 12:00:00')
            ],
            [
                new DateTime('2012-07-06 12:00:00'),
                MonthlyInterval::FREQUENCY_FIRST,
                [IntervalInterface::TUESDAY, IntervalInterface::THURSDAY],
                1,
                new DateTime('2012-07-05 12:00:00')
            ],
            // Second frequency
            [
                new DateTime('2012-04-11 12:00:00'),
                MonthlyInterval::FREQUENCY_SECOND,
                [IntervalInterface::TUESDAY],
                1,
                new DateTime('2012-04-10 12:00:00'),
            ],
            [
                new DateTime('2012-04-10 12:00:00'),
                MonthlyInterval::FREQUENCY_SECOND,
                [IntervalInterface::TUESDAY],
                1,
                new DateTime('2012-03-13 12:00:00'),
            ],
            // Third frequency
            [
                new DateTime('2012-04-19 12:00:00'),
                MonthlyInterval::FREQUENCY_THIRD,
                [IntervalInterface::TUESDAY],
                1,
                new DateTime('2012-04-17 12:00:00'),
            ],
            [
                new DateTime('2012-04-15 12:00:00'),
                MonthlyInterval::FREQUENCY_THIRD,
                [IntervalInterface::SUNDAY],
                1,
                new DateTime('2012-03-18 12:00:00'),
            ],
            // Fourth frequency
            [
                new DateTime('2012-04-26 12:00:00'),
                MonthlyInterval::FREQUENCY_FOURTH,
                [IntervalInterface::WEDNESDAY],
                1,
                new DateTime('2012-04-25 12:00:00'),
            ],
            [
                new DateTime('2012-04-15 12:00:00'),
                MonthlyInterval::FREQUENCY_FOURTH,
                [IntervalInterface::TUESDAY],
                1,
                new DateTime('2012-03-27 12:00:00'),
            ],
            // Fifth frequency
            [
                new DateTime('2012-04-19 12:00:00'),
                MonthlyInterval::FREQUENCY_FIFTH,
                [IntervalInterface::MONDAY],
                1,
                new DateTime('2012-01-30 12:00:00'),
            ],
            [
                new DateTime('2012-05-31 12:00:00'),
                MonthlyInterval::FREQUENCY_FIFTH,
                [IntervalInterface::WEDNESDAY],
                1,
                new DateTime('2012-05-30 12:00:00'),
            ],
            // Last frequency
            [
                new DateTime('2012-04-25 12:00:00'),
                MonthlyInterval::FREQUENCY_LAST,
                [IntervalInterface::TUESDAY],
                1,
                new DateTime('2012-04-24 12:00:00'),
            ],
            [
                new DateTime('2012-04-15 12:00:00'),
                MonthlyInterval::FREQUENCY_LAST,
                [IntervalInterface::MONDAY],
                1,
                new DateTime('2012-03-26 12:00:00')
            ],
            // Multiple days, different frequencies
            [
                new DateTime('2012-04-19 12:00:00'),
                MonthlyInterval::FREQUENCY_LAST,
                [IntervalInterface::TUESDAY, IntervalInterface::SATURDAY],
                1,
                new DateTime('2012-03-31 12:00:00'),
            ],
            [
                new DateTime('2012-04-24 12:00:00'),
                MonthlyInterval::FREQUENCY_LAST,
                [IntervalInterface::TUESDAY, IntervalInterface::MONDAY],
                1,
                new DateTime('2012-03-27 12:00:00'),
            ],
            [
                new DateTime('2012-04-30 12:00:00'),
                MonthlyInterval::FREQUENCY_LAST,
                [IntervalInterface::TUESDAY, IntervalInterface::MONDAY],
                1,
                new DateTime('2012-04-24 12:00:00'),
            ],
            // Multiple days, multiple months
            [
                new DateTime('2013-06-06 12:00:00'),
                MonthlyInterval::FREQUENCY_FIRST,
                [IntervalInterface::SATURDAY, IntervalInterface::SUNDAY],
                2,
                new DateTime('2013-06-02 12:00:00'),
            ],
            [
                new DateTime('2013-06-02 12:00:00'),
                MonthlyInterval::FREQUENCY_FIRST,
                [IntervalInterface::SATURDAY, IntervalInterface::SUNDAY],
                2,
                new DateTime('2013-06-01 12:00:00'),
            ],
            [
                new DateTime('2013-06-01 12:00:00'),
                MonthlyInterval::FREQUENCY_FIRST,
                [IntervalInterface::SATURDAY, IntervalInterface::SUNDAY],
                2,
                new DateTime('2013-04-07 12:00:00'),
            ],
            [
                new DateTime('2013-06-07 12:00:00'),
                MonthlyInterval::FREQUENCY_SECOND,
                [IntervalInterface::SATURDAY, IntervalInterface::THURSDAY],
                3,
                new DateTime('2013-03-14 12:00:00'),
            ],
            [
                new DateTime('2013-06-13 12:00:00'),
                MonthlyInterval::FREQUENCY_SECOND,
                [IntervalInterface::SATURDAY, IntervalInterface::THURSDAY],
                3,
                new DateTime('2013-06-08 12:00:00'),
            ],
            [
                new DateTime('2013-06-13 12:00:00'),
                MonthlyInterval::FREQUENCY_SECOND,
                [IntervalInterface::SUNDAY, IntervalInterface::TUESDAY],
                3,
                new DateTime('2013-06-11 12:00:00'),
            ],
            [
                new DateTime('2013-06-19 12:00:00'),
                MonthlyInterval::FREQUENCY_THIRD,
                [IntervalInterface::WEDNESDAY],
                11,
                new DateTime('2012-07-18 12:00:00'),
            ],
            [
                new DateTime('2013-06-19 12:00:00'),
                MonthlyInterval::FREQUENCY_FIFTH,
                [IntervalInterface::WEDNESDAY],
                6,
                new DateTime('2011-06-29 12:00:00'),
            ],
        ];
    }

    /**
     * @return array
     */
    public function ofEveryNMonthInvalidMethodsProvider()
    {
        return [
            ['ofEvery0thMonth'],
            ['ofEvery13thMonth'],
            ['ofEvery2rdMonth'],
            ['ofEvery11ndMonth'],
        ];
    }

    /**
     * @return array
     */
    public function ofEveryNMonthValidProvider()
    {
        return [
            [
                'ofEvery1stMonth',
                1
            ],
            [
                'ofEvery2ndMonth',
                2
            ],
            [
                'ofEveryOtherMonth',
                2
            ],
            [
                'ofEvery3rdMonth',
                3
            ],
            [
                'ofEvery4thMonth',
                4
            ],
            [
                'ofEvery5thMonth',
                5
            ],
            [
                'ofEvery6thMonth',
                6
            ],
            [
                'ofEvery7thMonth',
                7
            ],
            [
                'ofEvery8thMonth',
                8
            ],
            [
                'ofEvery9thMonth',
                9
            ],
            [
                'ofEvery10thMonth',
                10
            ],
            [
                'ofEvery11thMonth',
                11
            ],
            [
                'ofEvery12thMonth',
                12
            ],
        ];
    }

}
