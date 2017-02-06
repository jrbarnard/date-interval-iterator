<?php

use JRBarnard\DateIntervalIterator\Intervals\IntervalInterface;

/**
 * Class IntervalInterfaceTest
 */
class IntervalInterfaceTest extends TestCase
{
    // Tests:
    // - Days of week constant contains days of week
    // - Has constants forwards and backwards - done

    /** @test */
    public function has_direction_constants_and_wrapper()
    {
        $this->assertTrue(defined('InterfaceTestImplementation::FORWARDS'));
        $this->assertTrue(defined('InterfaceTestImplementation::FORWARDS'));
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