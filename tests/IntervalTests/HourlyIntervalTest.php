<?php
namespace JRBarnard\RecurrenceTests\IntervalTests;

use stdClass;
use DateTime;
use DateInterval;
use ReflectionClass;
use ReflectionObject;
use JRBarnard\RecurrenceTests\TestCase;
use JRBarnard\Recurrence\Intervals\HourlyInterval;
use JRBarnard\Recurrence\Exceptions\InvalidArgumentException;
use JRBarnard\Recurrence\Intervals\IntervalInterface;

/**
 * Class HourlyIntervalTest
 */
class HourlyIntervalTest extends TestCase
{
    const INTERVAL_CLASS = HourlyInterval::class;

    // Tests:
    // Hourly interval test constructor calls setNumberOfHours - done
    // setNumberOfHours only accepts numerical values - will throw if not - done
    // setNumberOfHours does not accept values less than or equal to zero - done
    // getNumberOfHours will return the set number of days - done
    // Find next occurrence will return the passed datetime + X hour(s) - done
    // can run backwards - done

    /** @test */
    public function can_run_interval_backwards()
    {
        $interval = $this->generateHourlyInterval();

        $interval->setNumberOfHours(10);

        $start = new DateTime();

        $cloned = clone $start;

        $expected = $cloned->sub(new DateInterval('PT10H'));

        $result = $interval->findNextOccurrence($start, IntervalInterface::BACKWARDS);

        $this->assertSame($expected->getTimestamp(), $result->getTimestamp());
    }

    /**
     * @dataProvider zeroOrLessNumberOfHoursProvider
     * @test
     * @param $zeroOrLessNumberOfHours
     */
    public function setNumberOfHours_only_accepts_values_greater_than_0($zeroOrLessNumberOfHours)
    {
        $interval = $this->generateHourlyInterval();

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('You must pass a number greater than 0 as the number of hours to the interval.');
        $interval->setNumberOfHours($zeroOrLessNumberOfHours);
    }

    /**
     * @dataProvider findNextOccurrenceProvider
     * @test
     * @param $originalDateTime DateTime
     * @param $numberOfHours
     * @param $nextDateTime DateTime
     */
    public function findNextOccurrence_will_return_the_passed_in_date_time_plus_set_hours($originalDateTime, $numberOfHours, $nextDateTime)
    {
        $interval = $this->generateHourlyInterval($numberOfHours);

        $next = $interval->findNextOccurrence($originalDateTime);

        $this->assertEquals($nextDateTime->getTimestamp(), $next->getTimestamp());
    }

    /** @test */
    public function getNumberOfHours_will_return_the_set_number_of_days()
    {
        $interval = $this->generateHourlyInterval();

        $numberOfHours = 10;
        $this->assertNotEquals($numberOfHours, $interval->getNumberOfHours());

        // Set and check has updated
        $interval->setNumberOfHours($numberOfHours);
        $this->assertEquals($numberOfHours, $interval->getNumberOfHours());
    }

    /**
     * @dataProvider invalidNumberOfHoursProvider
     * @test
     * @param $invalidNumberOfHours
     */
    public function if_passed_invalid_numeric_set_number_of_days_will_throw_exception($invalidNumberOfHours)
    {
        $interval = $this->generateHourlyInterval();

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('You must pass a numerical value as the number of hours to the interval.');
        $interval->setNumberOfHours($invalidNumberOfHours);
    }

    /**
     * @dataProvider validNumberOfHoursProvider
     * @test
     *
     * @param $numberOfHours
     * @param $expected
     */
    public function setNumberOfHours_will_accept_numeric_string_float_or_int($numberOfHours, $expected)
    {
        $interval = $this->generateHourlyInterval(22);

        $interval->setNumberOfHours($numberOfHours);

        // Use reflection to verify
        $class = new ReflectionObject($interval);
        $property = $class->getProperty('numberOfHours');
        $property->setAccessible(true);

        $this->assertSame($expected, $property->getValue($interval));
    }

    /** @test */
    public function hourly_interval_constructor_will_call_set_number_of_hours()
    {
        $className = self::INTERVAL_CLASS;
        $numberOfHours = 2;

        // Get mock, without the constructor being called
        $mock = $this->getMockBuilder($className)
            ->disableOriginalConstructor()
            ->setMethods([
                'setNumberOfHours'
            ])
            ->getMockForAbstractClass();

        // set expectations for constructor calls
        $mock->expects($this->once())
            ->method('setNumberOfHours')
            ->with($numberOfHours);

        // now call the constructor
        $reflectedClass = new ReflectionClass($className);
        $constructor = $reflectedClass->getConstructor();
        $constructor->invoke($mock, $numberOfHours);
    }

    /**
     * HELPERS AND PROVIDERS
     */

    /**
     * @return array
     */
    public function zeroOrLessNumberOfHoursProvider()
    {
        return [
            [0],
            [-10],
            [-1],
            [-999],
            ['-1'],
            ['0'],
            ['-9999'],
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
                1,
                new DateTime('2012-04-19 13:00:00'),
            ],
            [
                new DateTime('2012-04-19 12:00:00'),
                4,
                new DateTime('2012-04-19 16:00:00'),
            ],
            [
                new DateTime('2012-04-19 12:00:00'),
                4.5,
                new DateTime('2012-04-19 16:30:00'),
            ],
            [
                new DateTime('2012-04-19 12:00:00'),
                4.9,
                new DateTime('2012-04-19 16:54:00'),
            ],
            [
                new DateTime('2012-04-19 12:00:00'),
                2.8888,
                new DateTime('2012-04-19 14:53:00'), // will only do minutes
            ],
            [
                new DateTime('2012-04-19 12:00:00'),
                2.17391,
                new DateTime('2012-04-19 14:10:00'),
            ],
            [
                new DateTime('2012-04-19 12:00:00'),
                0.5,
                new DateTime('2012-04-19 12:30:00'),
            ],
            [
                // Show looping over a day
                new DateTime('2012-04-19 23:00:00'),
                4,
                new DateTime('2012-04-20 03:00:00'),
            ],
        ];
    }

    /**
     * @return array
     */
    public function invalidNumberOfHoursProvider()
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
    public function validNumberOfHoursProvider()
    {
        return [
            [0.5,0.5],
            [1,1.0],
            ["1",1.0],
            ["1.0", 1.0],
            [1.1, 1.1],
            [1.9, 1.9],
            [999,999.0],
            [1.27383,1.27383]
        ];
    }

    /**
     * @param int $numberOfDays
     *
     * @return HourlyInterval
     */
    protected function generateHourlyInterval($numberOfDays = 1)
    {
        $class = self::INTERVAL_CLASS;

        return new $class($numberOfDays);
    }
}