<?php
namespace JRBarnard\RecurrenceTests\IntervalTests;

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
     * TODO:
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

//    protected function generateMonthlyInterval($frequency = null, $days = [], $months = 1)
//    {
//        $class = self::INTERVAL_CLASS;
//
//        return new $class($frequency, $days, $months);
//    }

}
