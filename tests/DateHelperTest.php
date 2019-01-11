<?php
namespace JRBarnard\RecurrenceTests;

use DateTime;
use JRBarnard\Recurrence\DateHelper;
use JRBarnard\Recurrence\Intervals\IntervalInterface;
use JRBarnard\Recurrence\Exceptions\InvalidArgumentException;

/**
 * Class DateHelperTest
 */
class DateHelperTest extends TestCase
{
    // Tests:
    // getDayOfTheWeek will return int representation of day of the week - done
    // getTextOfDayOfTheWeek will return text of day of the week passed - done
    // setTimeFrom will set the time of the first datetime with the second - done

    /** @test */
    public function setTimeFrom_will_set_the_time_of_the_first_datetime_with_the_second()
    {
        $setTo = new DateTime('2012-12-12 12:00:00');
        $setFrom = new DateTime('2019-09-09 23:22:09');
        $result = DateHelper::setTimeFrom($setTo, $setFrom);

        $this->assertSame($setTo, $result);
        $this->assertSame('23:22:09', $setTo->format('H:i:s'));
    }

    /** @test */
    public function getTextOfDayOfTheWeek_will_throw_if_invalid()
    {
        $this->expectException(InvalidArgumentException::class);
        DateHelper::getTextOfDayOfTheWeek('invalid');
    }

    /** @test */
    public function getTextOfDayOfTheWeek_will_return_text_of_passed_day_of_week()
    {
        foreach (IntervalInterface::DAYS_OF_WEEK as $day) {
            $result = DateHelper::getTextOfDayOfTheWeek($day);

            $this->assertSame(IntervalInterface::DAYS_OF_WEEK_MAP[$day], $result);
        }
    }

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