<?php
namespace JRBarnard\Recurrence;

use DateTime;

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
}
