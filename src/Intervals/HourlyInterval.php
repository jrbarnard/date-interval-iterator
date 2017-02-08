<?php
namespace JRBarnard\Recurrence\Intervals;

use DateTime;
use JRBarnard\Recurrence\Exceptions\InvalidArgumentException;

/**
 * Class HourlyInterval
 * @package JRBarnard\Recurrence\Intervals
 */
class HourlyInterval implements IntervalInterface
{
    /**
     * @var float
     */
    protected $numberOfHours;

    /**
     * HourlyInterval constructor.
     *
     * @param $numberOfHours
     */
    public function __construct($numberOfHours = 1)
    {
        $this->setNumberOfHours($numberOfHours);
    }

    /**
     * @return float
     */
    public function getNumberOfHours()
    {
        return $this->numberOfHours;
    }

    /**
     * @param $numberOfHours
     *
     * @return $this
     */
    public function setNumberOfHours($numberOfHours)
    {
        if (!is_numeric($numberOfHours)) {
            throw new InvalidArgumentException(
                'You must pass a numerical value as the number of hours to the interval.'
            );
        }

        $numberOfHours = (float) $numberOfHours;

        if ($numberOfHours <= 0) {
            throw new InvalidArgumentException(
                'You must pass a number greater than 0 as the number of hours to the interval.'
            );
        }

        $this->numberOfHours = $numberOfHours;

        return $this;
    }

    /**
     * Method that finds the next occurrence of the interval from current
     *
     * @param DateTime $current
     * @param int $direction
     *
     * @return DateTime
     */
    public function findNextOccurrence(DateTime $current, $direction = self::FORWARDS)
    {
        $minutes = $this->getNumberOfHours() * 60;
        $interval = new \DateInterval('PT' . (int) $minutes . 'M');

        $cloned = clone $current;

        if ($direction === self::FORWARDS) {
            return $cloned->add($interval);
        } else {
            return $cloned->sub($interval);
        }
    }
}
