<?php
namespace JRBarnard\Recurrence\Intervals;

use DateTime;
use DateInterval;
use JRBarnard\Recurrence\DateHelper;
use JRBarnard\Recurrence\Exceptions\InvalidArgumentException;

/**
 * Class WeeklyInterval
 * @package JRBarnard\Recurrence\Intervals
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
     * WeeklyInterval constructor.
     *
     * @param array $days
     * @param int $weeks
     */
    public function __construct($days = [], $weeks = 1)
    {
        $this->setDays($days);
        $this->setWeeks($weeks);
    }

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
        $daysToSet = [];
        foreach ($days as $day) {
            if (!in_array($day, self::DAYS_OF_WEEK, true)) {
                throw new InvalidArgumentException($exceptionMessage);
            }

            // Only add in if not already added in
            if (!in_array($day, $daysToSet)) {
                $daysToSet[] = $day;
            }
        }

        // Sort before they go in
        sort($daysToSet);
        $this->days = $daysToSet;

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

        if (self::BACKWARDS === $direction) {
            $days = array_reverse($days);
        }

        $occurrence = null;

        foreach ($days as $day) {
            if (self::BACKWARDS === $direction) {
                $day = $day - $daysToAdd;
            } else {
                $day = $day + $daysToAdd;
            }

            if ((self::FORWARDS === $direction && $currentDayOfWeek <= $day) ||
                (self::BACKWARDS === $direction && $currentDayOfWeek >= $day)) {
                $daysToMove = $day - $currentDayOfWeek;

                // Get next date time by adding / subbing the difference in currentDay of week to next day of the week.
                $interval = new DateInterval('P' . abs($daysToMove) . 'D');
                $found = clone $current;

                // Depending on direction we will sub or add the interval
                if (self::BACKWARDS === $direction) {
                    $found = $found->sub($interval);
                } else {
                    $found = $found->add($interval);
                }

                // If the found is greater / less than the current (depending on direction) then we've actually found
                // and can break out of the loop.
                if ((self::BACKWARDS === $direction && $found->getTimestamp() < $current->getTimestamp()) ||
                    (self::FORWARDS === $direction && $found->getTimestamp() > $current->getTimestamp())) {
                    $occurrence = $found;
                    break;
                }
            }
        }

        if (!$occurrence instanceof DateTime) {
            $occurrence = $this->findNext($current, $direction, $daysToAdd + (7 * $this->getWeeks()));
        }

        return $occurrence;
    }
}
