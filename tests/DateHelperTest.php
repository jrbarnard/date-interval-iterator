<?php
use JRBarnard\Recurrence\DateHelper;
use JRBarnard\Recurrence\Intervals\IntervalInterface;

/**
 * Class DateHelperTest
 */
class DateHelperTest extends TestCase
{
    // Tests:
    // getDayOfTheWeek will return int representation of day of the week - done

    /**
     * @dataProvider dayOfTheWeekProvider
     * @test
     *
     * @param $datetime
     * @param $expected
     */
    public function getDayOfTheWeek_will_return_int_representation_of_day_of_the_week($datetime, $expected)
    {
        $result = DateHelper::getDayOfTheWeek($datetime);

        $this->assertTrue(is_int($result));
        $this->assertSame($expected, $result);
    }

    /**
     * HELPERS & PROVIDERS
     */

    /**
     * @return array
     */
    public function dayOfTheWeekProvider()
    {
        return [
            [
                new DateTime('2012-12-12'),
                IntervalInterface::WEDNESDAY
            ],
            [
                new DateTime('2012-12-14'),
                IntervalInterface::FRIDAY
            ],
            [
                new DateTime('2012-12-10'),
                IntervalInterface::MONDAY
            ],
            [
                new DateTime('2012-12-15'),
                IntervalInterface::SATURDAY
            ],
            [
                new DateTime('2012-12-16'),
                IntervalInterface::SUNDAY
            ],
            [
                new DateTime('2012-12-11'),
                IntervalInterface::TUESDAY
            ],
            [
                new DateTime('2012-12-13'),
                IntervalInterface::THURSDAY
            ],
        ];
    }
}