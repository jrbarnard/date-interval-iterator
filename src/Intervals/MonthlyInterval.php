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
 * Definition of month: https://www.merriam-webster.com/dictionary/calendar%20month
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
        self::FREQUENCY_FIFTH,
    ];

    const FREQUENCY_FIRST = 'first';
    const FREQUENCY_SECOND = 'second';
    const FREQUENCY_THIRD = 'third';
    const FREQUENCY_FOURTH = 'fourth';
    const FREQUENCY_FIFTH = 'fifth';
    const FREQUENCY_LAST = 'last';

    /**
     * @var int
     */
    protected $months = 1;

    /**
     * @var null|string
     */
    protected $frequency;

    /**
     * @var bool
     */
    protected $allowEmptyDays = true;

    /**
     * Day of the month of the original current
     * @var string
     */
    protected $originalDayOfMonth;

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
        if (!is_null($frequency) && (!is_string($frequency) || !in_array($frequency, self::FREQUENCIES))) {
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
     * Add or subtract from a date depending on direction
     *
     * @param DateTime $date
     * @param DateInterval $interval
     * @param $direction
     *
     * @return DateTime
     */
    public function addOrSub(DateTime $date, DateInterval $interval, $direction)
    {
        if ($direction === self::BACKWARDS) {
            return $date->sub($interval);
        }

        return $date->add($interval);
    }

    /**
     * Get the next frequency of the days / frequency for the current day starting with the passed next month
     *
     * @param DateTime $current
     * @param DateTime $currentMonth
     * @param $direction
     *
     * @return DateTime|null
     * @throws \Exception
     */
    protected function getNextFrequency(DateTime $current, DateTime $currentMonth, $direction)
    {
        $frequency = $this->getFrequency();
        $days = $this->getDays();

        $storedFrequencyNext = null;
        foreach ($days as $day) {
            // Modify to get the first day of the next month
            $frequencyNext = clone $currentMonth;
            $frequencyNext->modify(
                sprintf(
                    '%s %s of %s %s',
                    $frequency,
                    DateHelper::getTextOfDayOfTheWeek($day),
                    $currentMonth->format('F'),
                    $currentMonth->format('Y')
                )
            );
            DateHelper::setTimeFrom($frequencyNext, $current);

            // If we have a stored frequency next then we will compare our new frequency next and this to see
            // who comes first
            // Only set if the found next frequency is beyond/before current based on direction
            $frequencyNextTimestamp = $frequencyNext->getTimestamp();

            $shouldStore = !$storedFrequencyNext instanceof DateTime;
            if (!$shouldStore) {
                $storedFrequencyNextTimestamp = $storedFrequencyNext->getTimeStamp();

                $nextFrequencyBeyondOrBeforeStored = $direction === self::BACKWARDS ?
                    $frequencyNextTimestamp > $storedFrequencyNextTimestamp :
                    $frequencyNextTimestamp < $storedFrequencyNextTimestamp;

                // Store if (depending on direction) the next frequency found is before / beyond the stored
                // We want closest to the current so if backwards >, if forwards, <
                if ($nextFrequencyBeyondOrBeforeStored) {
                    $shouldStore = true;
                }
            }

            // Will store if nothing yet stored & if beyond / before current depending on direction
            $currentTimestamp = $current->getTimestamp();
            $nextFrequencyBeyondOrBeforeCurrent = $direction === self::BACKWARDS ?
                $frequencyNextTimestamp < $currentTimestamp :
                $frequencyNextTimestamp > $currentTimestamp;

            if ($shouldStore && $nextFrequencyBeyondOrBeforeCurrent) {
                $storedFrequencyNext = $frequencyNext;
            }
        }

        //
        // If we have jumped a month or it's on the incorrect day, try next month (recursive)
        if ($storedFrequencyNext instanceof DateTime) {
            $storedFrequencyMonth = $storedFrequencyNext->format('n');
            $currentMonthMonth = $currentMonth->format('n');
            $storedFrequencyDayOfWeek = $storedFrequencyNext->format('w');

            if ($storedFrequencyMonth !== $currentMonthMonth || !in_array($storedFrequencyDayOfWeek, $days)) {
                $storedFrequencyNext = null;
            }
        }

        // If we still don't have one set attempt to get next from the following month
        if (!$storedFrequencyNext instanceof DateTime) {
            $nextMonth = clone $currentMonth;
            $this->addOrSub($nextMonth, new DateInterval('P' . $this->getMonths() . 'M'), $direction);

            $storedFrequencyNext = $this->getNextFrequency(
                $current,
                $nextMonth,
                $direction
            );
        }

        return $storedFrequencyNext;
    }

    /**
     * Method that finds the next occurrence of the interval from current
     *
     * @param DateTime $current
     * @param $direction
     *
     * @return DateTime
     * @throws \Exception
     */
    public function findNextOccurrence(DateTime $current, $direction = self::FORWARDS)
    {
        // Get day of the month for current and store if we don't have one
        if (is_null($this->originalDayOfMonth)) {
            $this->originalDayOfMonth = $current->format('j');
        }

        $basicMonthDateInterval = new DateInterval('P' . $this->getMonths() . 'M');

        // Get next month (first of the month)
        $firstOfThisMonth = clone $current;
        $firstOfThisMonth->modify('first day of this month');
        $firstOfNextMonth = clone $firstOfThisMonth;
        $this->addOrSub($firstOfNextMonth, $basicMonthDateInterval, $direction);

        // If we are looking for days / frequency
        if (!empty($days = $this->getDays())) {
            if (is_null($frequency = $this->getFrequency())) {
                throw new InvalidArgumentException('You must set a frequency and days together');
            }

            $storedFrequencyNext = $this->getNextFrequency(
                $current,
                $firstOfThisMonth,
                $direction
            );

            // If we don't have a stored frequency next set then try the next month interval
            if (!$storedFrequencyNext) {
                $storedFrequencyNext = $this->getNextFrequency(
                    $current,
                    $firstOfNextMonth,
                    $direction
                );
            }

            $next = $storedFrequencyNext;
        } else {
            // Basic month intervals

            // If the next month has less days than the current day of the month then we will force to be the max
            $numberOfDaysInNextMonth = $firstOfNextMonth->format('t');
            $monthDayToSet = $this->originalDayOfMonth;

            // If the next month doesn't have the required number of days that we want, then cap it at the
            // available number of days
            if ($numberOfDaysInNextMonth < $monthDayToSet) {
                $monthDayToSet = $numberOfDaysInNextMonth;
            }

            $firstOfNextMonth->setDate(
                $firstOfNextMonth->format('Y'),
                $firstOfNextMonth->format('m'),
                $monthDayToSet // Force day of the month
            );

            $next = $firstOfNextMonth;
        }

        return $next;
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
