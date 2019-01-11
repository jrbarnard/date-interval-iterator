<?php
namespace JRBarnard\RecurrenceTests\IntervalTests;

use stdClass;
use DateTime;
use ReflectionObject;
use JRBarnard\RecurrenceTests\TestCase;
use JRBarnard\Recurrence\Intervals\SetsDays;
use JRBarnard\Recurrence\Intervals\IntervalInterface;
use JRBarnard\Recurrence\Exceptions\InvalidArgumentException;

class SetsDaysTraitTest extends TestCase
{
    /**
     * Tests:
     * - setDays
     *      - will return self - done
     *      - will set days prop - done
     *      - will throw if invalid days - done
     *      - if set same days, will only set one - done
     *      - won't allow empty unless allowEmptyDays set on class - done
     *  - getDays
     *      - will return array of days set - done
     */

    /** @test */
    public function setDays_will_allow_empty_with_allowEmptyDays_set_on_class()
    {
        $interval = new SetsDaysTraitAllowEmptyDaysTest();
        $this->assertSame($interval, $interval->setDays([]));
    }

    /** @test */
    public function setDays_wont_allow_empty_by_default()
    {
        $this->expectException(InvalidArgumentException::class);
        $interval = new SetsDaysMockInterval();
        $interval->setDays([]);
    }

    /** @test */
    public function setDays_will_return_self()
    {
        $interval = new SetsDaysMockInterval();

        $this->assertSame($interval, $interval->setDays(IntervalInterface::MONDAY));
    }

    /** @test */
    public function getDays_will_return_set_days()
    {
        $interval = new SetsDaysMockInterval();

        $expected = IntervalInterface::THURSDAY;
        $interval->setDays($expected);

        $this->assertSame([$expected], $interval->getDays());
    }

    /** @test */
    public function setDays_accepts_a_single_day_of_the_week()
    {
        $interval = new SetsDaysMockInterval();

        $expected = IntervalInterface::THURSDAY;
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
        $interval = new SetsDaysMockInterval;

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage(
            'You must pass valid days of the week to the interval'
        );
        $interval->setDays($invalidDays);
    }

    /** @test */
    public function if_set_same_days_will_only_set_once()
    {
        $days = [
            IntervalInterface::SATURDAY,
            IntervalInterface::SATURDAY,
            IntervalInterface::SUNDAY,
        ];

        $interval = new SetsDaysMockInterval();
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
            IntervalInterface::SATURDAY, // 6
            IntervalInterface::WEDNESDAY, // 3
            IntervalInterface::MONDAY, // 1
            IntervalInterface::THURSDAY, // 4
        ];

        $expectedOrder = [
            IntervalInterface::MONDAY, // 1
            IntervalInterface::WEDNESDAY, // 3
            IntervalInterface::THURSDAY, // 4
            IntervalInterface::SATURDAY, // 6
        ];

        $interval = new SetsDaysMockInterval();

        $interval->setDays($days);

        // Check set in order
        $class = new ReflectionObject($interval);
        $property = $class->getProperty('days');
        $property->setAccessible(true);

        $this->assertSame($expectedOrder, $property->getValue($interval));
    }

    /**
     * HELPERS & PROVIDERS
     */
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
                IntervalInterface::MONDAY,
                IntervalInterface::THURSDAY,
                IntervalInterface::SUNDAY,
                99,
                'test'
            ]]
        ];
    }

}

class SetsDaysTraitAllowEmptyDaysTest implements IntervalInterface
{
    use SetsDays;

    protected $allowEmptyDays = true;

    /**
     * Method that finds the next occurrence of the interval from current
     *
     * @param DateTime $current
     * @param $direction
     *
     * @return DateTime
     */
    public function findNextOccurrence(DateTime $current, $direction = self::FORWARDS)
    {
        // TODO: Implement findNextOccurrence() method.
    }
}

class SetsDaysMockInterval implements IntervalInterface
{
    use SetsDays;

    /**
     * Method that finds the next occurrence of the interval from current
     *
     * @param DateTime $current
     * @param $direction
     *
     * @return DateTime
     */
    public function findNextOccurrence(DateTime $current, $direction = self::FORWARDS)
    {
        // TODO: Implement findNextOccurrence() method.
    }
}
