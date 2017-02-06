<?php

use JRBarnard\DateIntervalIterator\Intervals\IntervalInterface;

/**
 * Class IntervalInterfaceTest
 */
class IntervalInterfaceTest extends TestCase
{
    // Tests:
    // - Days of week constant contains days of week - done
    // - Has constants forwards and backwards - done

    /** @test */
    public function has_days_of_week_constant_group_and_weekday_helper()
    {
        $this->assertTrue(defined('InterfaceTestImplementation::MONDAY'));
        $this->assertTrue(defined('InterfaceTestImplementation::TUESDAY'));
        $this->assertTrue(defined('InterfaceTestImplementation::WEDNESDAY'));
        $this->assertTrue(defined('InterfaceTestImplementation::THURSDAY'));
        $this->assertTrue(defined('InterfaceTestImplementation::FRIDAY'));
        $this->assertTrue(defined('InterfaceTestImplementation::SATURDAY'));
        $this->assertTrue(defined('InterfaceTestImplementation::SUNDAY'));

        $this->assertTrue(is_int(InterfaceTestImplementation::MONDAY));
        $this->assertTrue(is_int(InterfaceTestImplementation::TUESDAY));
        $this->assertTrue(is_int(InterfaceTestImplementation::WEDNESDAY));
        $this->assertTrue(is_int(InterfaceTestImplementation::THURSDAY));
        $this->assertTrue(is_int(InterfaceTestImplementation::FRIDAY));
        $this->assertTrue(is_int(InterfaceTestImplementation::SATURDAY));
        $this->assertTrue(is_int(InterfaceTestImplementation::SUNDAY));
        $this->assertTrue(is_array(InterfaceTestImplementation::DAYS_OF_WEEK));
        $this->assertTrue(is_array(InterfaceTestImplementation::WEEKDAYS));

        $this->assertCount(7, InterfaceTestImplementation::DAYS_OF_WEEK);
        $this->assertCount(5, InterfaceTestImplementation::WEEKDAYS);

        $daysOfWeek = InterfaceTestImplementation::DAYS_OF_WEEK;
        $this->assertTrue(in_array(InterfaceTestImplementation::MONDAY, $daysOfWeek));
        $this->assertTrue(in_array(InterfaceTestImplementation::TUESDAY, $daysOfWeek));
        $this->assertTrue(in_array(InterfaceTestImplementation::WEDNESDAY, $daysOfWeek));
        $this->assertTrue(in_array(InterfaceTestImplementation::THURSDAY, $daysOfWeek));
        $this->assertTrue(in_array(InterfaceTestImplementation::FRIDAY, $daysOfWeek));
        $this->assertTrue(in_array(InterfaceTestImplementation::SATURDAY, $daysOfWeek));
        $this->assertTrue(in_array(InterfaceTestImplementation::SUNDAY, $daysOfWeek));

        $weekdays = InterfaceTestImplementation::WEEKDAYS;
        $this->assertTrue(in_array(InterfaceTestImplementation::MONDAY, $weekdays));
        $this->assertTrue(in_array(InterfaceTestImplementation::TUESDAY, $weekdays));
        $this->assertTrue(in_array(InterfaceTestImplementation::WEDNESDAY, $weekdays));
        $this->assertTrue(in_array(InterfaceTestImplementation::THURSDAY, $weekdays));
        $this->assertTrue(in_array(InterfaceTestImplementation::FRIDAY, $weekdays));
    }

    /** @test */
    public function has_direction_constants_and_wrapper()
    {
        $this->assertTrue(defined('InterfaceTestImplementation::FORWARDS'));
        $this->assertTrue(defined('InterfaceTestImplementation::BACKWARDS'));
        $this->assertTrue(defined('InterfaceTestImplementation::DIRECTIONS'));

        // Check valid types
        $this->assertTrue(is_int(InterfaceTestImplementation::FORWARDS));
        $this->assertTrue(is_int(InterfaceTestImplementation::BACKWARDS));
        $this->assertTrue(is_array(InterfaceTestImplementation::DIRECTIONS));

        // Check directions only includes forwards and backwards
        $directions = InterfaceTestImplementation::DIRECTIONS;
        $this->assertCount(2, $directions);
        $this->assertTrue(in_array(InterfaceTestImplementation::FORWARDS, $directions));
        $this->assertTrue(in_array(InterfaceTestImplementation::BACKWARDS, $directions));
    }

    /**
     * HELPERS & PROVIDERS
     */

    /**
     * @return InterfaceTestImplementation
     */
    protected function generateInterval()
    {
        return new InterfaceTestImplementation();
    }
}

class InterfaceTestImplementation implements IntervalInterface
{
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
        return $current;
    }
}