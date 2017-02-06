<?php
namespace JRBarnard\DateIntervalIterator\Intervals;

use DateTime;
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
     * @param int $weeks
     *
     * @return $this
     */
    public function setWeeks($weeks = 1)
    {
        if (
            !is_numeric($weeks) ||
            $weeks < 1
        ) {
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
        // TODO:
        return $current;
    }
}