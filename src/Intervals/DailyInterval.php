<?php
namespace JRBarnard\Recurrence\Intervals;

use DateTime;
use DateInterval;
use JRBarnard\Recurrence\Exceptions\InvalidArgumentException;

/**
 * Class DailyInterval
 * @package JRBarnard\Recurrence\Intervals
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
                'You must pass a numerical value as the number of days to the interval.'
            );
        }

        $numberOfDays = (int) $numberOfDays;

        if ($numberOfDays < 1) {
            throw new InvalidArgumentException(
                'You must pass a number greater than 1 as the number of days to the interval.'
            );
        }

        $this->numberOfDays = $numberOfDays;

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
     * @param $direction
     *
     * @return DateTime
     */
    public function findNextOccurrence(DateTime $current, $direction = self::FORWARDS)
    {
        $interval = new DateInterval('P' . $this->getNumberOfDays() . 'D');

        $cloned = clone $current;

        if ($direction === self::FORWARDS) {
            return $cloned->add($interval);
        } else {
            return $cloned->sub($interval);
        }
    }
}
