<?php

use JRBarnard\Recurrence\Intervals\DailyInterval;
use JRBarnard\Recurrence\Exceptions\InvalidArgumentException;
use JRBarnard\Recurrence\Intervals\IntervalInterface;

/**
 * Class DailyIntervalTest
 */
class DailyIntervalTest extends TestCase
{
    const INTERVAL_CLASS = DailyInterval::class;

    // Tests:
    // Daily interval test constructor calls setNumberOfDays - done
    // setNumberOfDays only accepts numerical values - will throw if not - done
    // setNumberOfDays does not accept values less than 1 - done
    // setNumberOfDays will cast numeric strings to ints - done
    // getNumberOfDays will return the set number of days - done
    // Find next occurrence will return the passed datetime + X day(s) - done
    // can run backwards - done

    /** @test */
    public function can_run_interval_backwards()
    {
        $interval = $this->generateDailyInterval();

        $interval->setNumberOfDays(10);

        $start = new DateTime();

        $cloned = clone $start;

        $expected = $cloned->sub(new DateInterval('P10D'));

        $result = $interval->findNextOccurrence($start, IntervalInterface::BACKWARDS);

        $this->assertSame($expected->getTimestamp(), $result->getTimestamp());
    }

    /**
     * @dataProvider lessThanOneNumberOfDaysProvider
     * @test
     * @param $lessThanOneNumberOfDays
     */
    public function setNumberOfDays_only_accepts_values_greater_than_1($lessThanOneNumberOfDays)
    {
        $interval = $this->generateDailyInterval();

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('You must pass a number greater than 1 as the number of days to the interval.');
        $interval->setNumberOfDays($lessThanOneNumberOfDays);
    }

    /**
     * @dataProvider findNextOccurrenceProvider
     * @test
     * @param $originalDateTime DateTime
     * @param $numberOfDays
     * @param $nextDateTime DateTime
     */
    public function findNextOccurrence_will_return_the_passed_in_date_time_plus_set_days($originalDateTime, $numberOfDays, $nextDateTime)
    {
        $interval = $this->generateDailyInterval($numberOfDays);

        $next = $interval->findNextOccurrence($originalDateTime);

        $this->assertEquals($nextDateTime->getTimestamp(), $next->getTimestamp());
    }

    /** @test */
    public function getNumberOfDays_will_return_the_set_number_of_days()
    {
        $interval = $this->generateDailyInterval();

        $numberOfDays = 10;
        $this->assertNotEquals($numberOfDays, $interval->getNumberOfDays());

        // Set and check has updated
        $interval->setNumberOfDays($numberOfDays);
        $this->assertEquals($numberOfDays, $interval->getNumberOfDays());
    }

    /**
     * @dataProvider invalidNumberOfDaysProvider
     * @test
     * @param $invalidNumberOfDays
     */
    public function if_passed_invalid_numeric_set_number_of_days_will_throw_exception($invalidNumberOfDays)
    {
        $interval = $this->generateDailyInterval();

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('You must pass a numerical value as the number of days to the interval.');
        $interval->setNumberOfDays($invalidNumberOfDays);
    }

    /**
     * @dataProvider validNumberOfDaysProvider
     * @test
     *
     * @param $numberOfDays
     * @param $expected
     */
    public function setNumberOfDays_will_accept_numeric_string_float_or_int_will_cast_to_int($numberOfDays, $expected)
    {
        $interval = $this->generateDailyInterval(22);

        $interval->setNumberOfDays($numberOfDays);

        // Use reflection to verify
        $class = new ReflectionObject($interval);
        $property = $class->getProperty('numberOfDays');
        $property->setAccessible(true);

        $this->assertSame($expected, $property->getValue($interval));
    }

    /** @test */
    public function daily_interval_constructor_will_call_set_number_of_days()
    {
        $className = self::INTERVAL_CLASS;
        $numberOfDays = 2;

        // Get mock, without the constructor being called
        $mock = $this->getMockBuilder($className)
            ->disableOriginalConstructor()
            ->setMethods([
                'setNumberOfDays'
            ])
            ->getMockForAbstractClass();

        // set expectations for constructor calls
        $mock->expects($this->once())
            ->method('setNumberOfDays')
            ->with($numberOfDays);

        // now call the constructor
        $reflectedClass = new ReflectionClass($className);
        $constructor = $reflectedClass->getConstructor();
        $constructor->invoke($mock, $numberOfDays);
    }

    /**
     * HELPERS AND PROVIDERS
     */

    /**
     * @return array
     */
    public function lessThanOneNumberOfDaysProvider()
    {
        return [
            [0],
            [-10],
            [-1],
            [-999],
            ['-1'],
            ['0'],
            ['-9999'],
            [0.999999],
            ['0.999999']
        ];
    }

    /**
     * @return array
     */
    public function findNextOccurrenceProvider()
    {
        return [
            [
                new DateTime('2012-04-19'),
                1,
                new DateTime('2012-04-20'),
            ],
            [
                new DateTime('2012-04-30'),
                1,
                new DateTime('2012-05-01'),
            ],
            [
                new DateTime('2012-02-29'),
                1,
                new DateTime('2012-03-01'),
            ],
            [
                new DateTime('2012-02-29'),
                10,
                new DateTime('2012-03-10'),
            ],
            [
                new DateTime('2012-01-02'),
                22,
                new DateTime('2012-01-24'),
            ],
            [
                new DateTime('2012-07-12'),
                300,
                new DateTime('2013-05-08'),
            ]
        ];
    }

    /**
     * @return array
     */
    public function invalidNumberOfDaysProvider()
    {
        return [
            [new stdClass()],
            [[]],
            [['test', 1]],
            ['test'],
            [true],
            [false],
            [null]
        ];
    }

    /**
     * @return array
     */
    public function validNumberOfDaysProvider()
    {
        return [
            [1,1],
            ["1",1],
            ["1.0", 1],
            [1.1, 1],
            [1.9, 1],
            [999,999]
        ];
    }

    /**
     * @param int $numberOfDays
     *
     * @return DailyInterval
     */
    protected function generateDailyInterval($numberOfDays = 1)
    {
        $class = self::INTERVAL_CLASS;

        return new $class($numberOfDays);
    }
}