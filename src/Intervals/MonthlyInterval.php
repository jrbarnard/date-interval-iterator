<?php
namespace JRBarnard\Recurrence\Intervals;

use DateTime;
use DateInterval;
use JRBarnard\Recurrence\DateHelper;
use JRBarnard\Recurrence\Exceptions\InvalidArgumentException;

/**
 * Class MonthlyInterval
 *
 * @package JRBarnard\Recurrence\Intervals
 *
 * Available magic methods:
 */
class MonthlyInterval implements IntervalInterface
{
    use SetsDays;

    const FREQUENCIES = [
        self::FREQUENCY_FIRST,
        self::FREQUENCY_SECOND,
        self::FREQUENCY_THIRD,
        self::FREQUENCY_FOURTH,
        self::FREQUENCY_LAST,
    ];

    const FREQUENCY_FIRST = 1;
    const FREQUENCY_SECOND = 2;
    const FREQUENCY_THIRD = 3;
    const FREQUENCY_FOURTH = 4;
    const FREQUENCY_LAST = 'last';

    /**
     * @var int
     */
    protected $months = 1;

    /**
     * @var mixed
     */
    protected $frequency;

    /**
     * @var bool
     */
    protected $allowEmptyDays = true;

    /**
     * WeeklyInterval constructor.
     *
     * @param null $frequency
     * @param array $days
     * @param int $months
     */
    public function __construct($frequency = null, $days = [], $months = 1)
    {
        $this->setFrequency($frequency);
        $this->setDays($days);
        $this->setMonths($months);
    }

    /**
     * @param $frequency
     *
     * @return $this
     */
    public function setFrequency($frequency)
    {
        if (!is_null($frequency) && !in_array($frequency, self::FREQUENCIES)) {
            throw new InvalidArgumentException('Frequency must be a valid frequency or null');
        }

        $this->frequency = $frequency;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getFrequency()
    {
        return $this->frequency;
    }

    /**
     * @param int $months
     *
     * @return $this
     */
    public function setMonths($months = 1)
    {
        if (!is_int($months) || $months < 1 || $months > 12) {
            throw new InvalidArgumentException(
                'You must pass a valid number of months to the interval'
            );
        }

        $this->months = $months;

        return $this;
    }

    /**
     * @return int
     */
    public function getMonths()
    {
        return $this->months;
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

//    /**
//     * @param $name
//     * @param $arguments
//     *
//     * @return mixed
//     */
//    public function __call($name, $arguments)
//    {
//        $everyPrefix = 'every';
//        $andEveryPrefix = 'andEvery';
//        $ofEveryPrefix = 'ofEvery';
//        $ofEverySuffix = 'Week';
//
//        // Magic call to every{day} methods
//        if (false !== strpos($name, $everyPrefix, 0)) {
//            $day = $this->getDayFromMethod($name, $everyPrefix);
//            if (false !== $day) {
//                $this->setDays($day);
//
//                return $this;
//            }
//        }
//
//        // Magic call to andEvery{day} methods
//        if (false !== strpos($name, $andEveryPrefix, 0)) {
//            $day = $this->getDayFromMethod($name, $andEveryPrefix);
//            if (false !== $day) {
//                $this->setDays(array_merge($this->getDays(), [$day]));
//
//                return $this;
//            }
//        }
//
//        // Magic call to ofEvery{week}Week methods
//        $ofEveryPrefixLen = strlen($ofEveryPrefix);
//        $ofEverySuffixLen = strlen($ofEverySuffix);
//
//        // Starts with ofEvery, ends with Week
//        if (false !== strpos($name, $ofEveryPrefix) && $ofEverySuffix === substr($name, -1 * $ofEverySuffixLen)) {
//            // Get the bit in between and cast (relies on php's internal type juggling)
//            $weeks = (int) substr($name, $ofEveryPrefixLen, -1 * $ofEverySuffixLen);
//
//            if ($weeks > 0) {
//                $this->ofEveryWeek($weeks);
//
//                return $this;
//            }
//        }
//
//        throw new BadMethodCallException('Call to undefined method {' . __CLASS__ . '}::{' . $name . '}()');
//    }

//    /**
//     * @param $methodName
//     * @param $prefix
//     *
//     * @return bool|int
//     */
//    protected function getDayFromMethod($methodName, $prefix)
//    {
//        $day = strtoupper(substr($methodName, strlen($prefix)));
//
//        if (!defined('self::' . $day)) {
//            return false;
//        }
//
//        $day = constant('self::' . $day);
//
//        if (!$this->isValidDay($day)) {
//            return false;
//        }
//
//        return $day;
//    }
}
