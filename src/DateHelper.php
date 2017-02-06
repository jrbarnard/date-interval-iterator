<?php
namespace JRBarnard\DateIntervalIterator;

use DateTime;

/**
 * Class DateHelper
 * @package JRBarnard\DateIntervalIterator
 */
class DateHelper
{
    /**
     * @param DateTime $dateTime
     *
     * @return string
     */
    public static function getDayOfTheWeek(DateTime $dateTime)
    {
        return (int) $dateTime->format('w');
    }
}