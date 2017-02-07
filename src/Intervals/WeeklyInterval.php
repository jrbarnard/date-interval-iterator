<?php
namespace JRBarnard\DateIntervalIterator\Intervals;

use DateTime;
use DateInterval;
use JRBarnard\DateIntervalIterator\DateHelper;
use JRBarnard\DateIntervalIterator\Exceptions\InvalidArgumentException;

/**
 * Class WeeklyInterval
 * @package JRBarnard\DateIntervalIterator\Intervals
 */
class WeeklyInterval implements IntervalInterface
{
    /**
     * @var array
     */
    protected $days = [];

    /**
     * @var int
     */
    protected $weeks = 1;

    /**
     * Storage for usage during the find next loop
     * @var
     */
    protected $lastSetDayOfWeek;

    /**
     * @param int $weeks
     *
     * @return $this
     */
    public function setWeeks($weeks = 1)
    {
        if (!is_numeric($weeks) || $weeks < 1) {
            throw new InvalidArgumentException(
                'You must pass a valid number of weeks to the interval'
            );
        }

        $this->weeks = (int) $weeks;

        return $this;
    }

    /**
     * @param array $days
     *
     * @return $this
     */
    public function setDays($days = self::DAYS_OF_WEEK)
    {
        $days = (array) $days;

        $exceptionMessage = 'You must pass valid days of the week to the interval';

        if (empty($days)) {
            throw new InvalidArgumentException($exceptionMessage);
        }

        // Loop over the days passed in and verify all are valid
        foreach ($days as $day) {
            if (!in_array($day, self::DAYS_OF_WEEK, true)) {
                throw new InvalidArgumentException($exceptionMessage);
            }
        }

        // Sort before they go in
        sort($days);
        $this->days = $days;

        return $this;
    }

    /**
     * @return array
     */
    public function getDays()
    {
        return $this->days;
    }

    /**
     * @return int
     */
    public function getWeeks()
    {
        return $this->weeks;
    }

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
        return $this->findNext($current, $direction);
    }

    /**
     * @param DateTime $current
     * @param $direction
     * @param int $daysToAdd
     *
     * @return DateTime
     */
    protected function findNext(DateTime $current, $direction, $daysToAdd = 0)
    {
        $days = $this->getDays();
        $currentDayOfWeek = DateHelper::getDayOfTheWeek($current);

        if ($direction === self::BACKWARDS) {
            $days = array_reverse($days);
        }

        $occurrence = null;

        $count = 0;
        foreach ($days as $day) {
            $day += $daysToAdd;

            // Is the current day of the week less than or same as the iterated day?
            // And has it already been set?
            if ($currentDayOfWeek <= $day && $day !== $this->lastSetDayOfWeek) {

                // Get next date time by adding the difference in currentDay of week to next day of the week
                // TODO: extract into helper?
                // TODO: Get working in reverse
                $occurrence = (clone $current)->add(new DateInterval('P' . $day - $currentDayOfWeek . 'D'));
                $occurrenceDayOfWeek = DateHelper::getDayOfTheWeek($occurrence);

                // If it's the not on the same day or it is and it's in the future from the current occurrence,
                // then we've got our next occurrence and can break the loop.
                if ($occurrenceDayOfWeek !== $day) {
                    // set the day of week flag
                    $this->lastSetDayOfWeek = $occurrenceDayOfWeek;
                    break;
                }
            }
            $count++;

            // If we've reached the end of the array, add / remove N week(s) and reset the days array as we don't yet have
            // an occurrence and will look to next / previous week.
            if ($count >= $daysToAdd) {
                // TODO: Get working in reverse
                $occurrence = $this->findNext($current, $direction, $daysToAdd + (7 * $this->getWeeks()));
            }
        }

        return $occurrence;
    }
}
