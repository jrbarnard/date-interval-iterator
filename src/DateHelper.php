<?php
namespace JRBarnard\Recurrence;

use DateTime;
use JRBarnard\Recurrence\Intervals\IntervalInterface;
use JRBarnard\Recurrence\Exceptions\InvalidArgumentException;

/**
 * Class DateHelper
 * @package JRBarnard\Recurrence
 */
class DateHelper
{
    /**
     * @param DateTime $dateTime
     *
     * @return int
     */
    public static function getDayOfTheWeek(DateTime $dateTime)
    {
        return (int) $dateTime->format('w');
    }

    /**
     * @param $day
     *
     * @return mixed
     */
    public static function getTextOfDayOfTheWeek($day)
    {
        if (array_key_exists($day, IntervalInterface::DAYS_OF_WEEK_MAP)) {
            return IntervalInterface::DAYS_OF_WEEK_MAP[$day];
        }

        throw new InvalidArgumentException('Invalid day number passed');
    }

    /**
     * @param DateTime $toSet
     * @param DateTime $setFrom
     *
     * @return DateTime
     */
    public static function setTimeFrom(DateTime $toSet, DateTime $setFrom)
    {
        $toSet->setTime(
            $setFrom->format('H'),
            $setFrom->format('i'),
            $setFrom->format('s')
        );

        return $toSet;
    }
}
