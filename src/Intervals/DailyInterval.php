<?php
namespace JRBarnard\DateIntervalIterator\Intervals;

use DateTime;
use DateInterval;
use JRBarnard\DateIntervalIterator\DateIntervalIterator;
use JRBarnard\DateIntervalIterator\Exceptions\InvalidArgumentException;

/**
 * Class DailyInterval
 * @package JRBarnard\DateIntervalIterator\Intervals
 */
class DailyInterval implements IntervalInterface
{
    /**
     * @var int
     */
    protected $numberOfDays;

    /**
     * DailyInterval constructor.
     *
     * @param int $numberOfDays
     */
    public function __construct($numberOfDays = 1)
    {
        $this->setNumberOfDays($numberOfDays);
    }

    /**
     * @param $numberOfDays
     *
     * @return $this
     */
    public function setNumberOfDays($numberOfDays)
    {
        if (!is_numeric($numberOfDays)) {
            throw new InvalidArgumentException(
                'You must pass a valid integer as the number of days to the interval.'
            );
        }

        $this->numberOfDays = (int) $numberOfDays;

        return $this;
    }

    /**
     * @return int
     */
    public function getNumberOfDays()
    {
        return $this->numberOfDays;
    }

    /**
     * Method that finds the next occurrence of the interval from current
     *
     * @param DateTime $current
     * @param DateIntervalIterator $iterator
     *
     * @return DateTime
     */
    public function findNextOccurrence(DateTime $current, DateIntervalIterator $iterator)
    {
        return (clone $current)->add(new DateInterval('P' . $this->getNumberOfDays() . 'D'));
    }
}

