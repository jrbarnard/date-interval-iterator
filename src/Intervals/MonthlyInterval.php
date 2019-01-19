<?php
namespace JRBarnard\Recurrence\Intervals;

use DateTime;
use DateInterval;
use NumberFormatter;
use JRBarnard\Recurrence\DateHelper;
use JRBarnard\Recurrence\Exceptions\BadMethodCallException;
use JRBarnard\Recurrence\Exceptions\InvalidArgumentException;

/**
 * Class MonthlyInterval
 *
 * @package JRBarnard\Recurrence\Intervals
 * Definition of month: https://www.merriam-webster.com/dictionary/calendar%20month
 *
 * Available magic methods:
 *
 * Frequency setters:
 * @method everyFirst
 * @method everySecond
 * @method everyThird
 * @method everyFourth
 * @method everyFifth
 * @method everyLast
 *
 * Day setters
 * @method monday
 * @method tuesday
 * @method wednesday
 * @method thursday
 * @method friday
 * @method saturday
 * @method sunday
 * @method andMonday
 * @method andTuesday
 * @method andWednesday
 * @method andThursday
 * @method andFriday
 * @method andSaturday
 * @method andSunday
 *
 * Month setters:
 * @method ofEvery2ndMonth
 * @method ofEveryOtherMonth
 * @method ofEvery3rdMonth
 * @method ofEvery4thMonth
 * @method ofEvery5thMonth
 * @method ofEvery6thMonth
 * @method ofEvery7thMonth
 * @method ofEvery8thMonth
 * @method ofEvery9thMonth
 * @method ofEvery10thMonth
 * @method ofEvery11thMonth
 * @method ofEvery12thMonth
 *
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

    /**
     * @param $frequency
     * @param $days
     *
     * @return $this
     */
    public function every($frequency, $days)
    {
        return $this->setDays($days)
            ->setFrequency($frequency);
    }

    /**
     * @param $month
     *
     * @return MonthlyInterval
     */
    public function ofEveryMonth($month)
    {
        return $this->setMonths($month);
    }

    /**
     * @param $name
     * @param $arguments
     *
     * @return mixed
     */
    public function __call($name, $arguments)
    {
        // Anon function to get the FQN for a class constant
        $getConstantName = function ($class, $constant) {
            return sprintf(
                '%s::%s',
                $class,
                $constant
            );
        };

        $everyPrefix = 'every';
        $andPrefix = 'and';
        $ofEveryPrefix = 'ofEvery';
        $monthSuffix = 'Month';

        /**
         * Check if we have a specific prefix
         * @param $string
         * @param $prefix
         *
         * @return bool
         */
        $hasPrefix = function ($string, $prefix) {
            return false !== strpos($string, $prefix, 0);
        };

        /**
         * Check if we have a specific suffix
         * @param $string
         * @param $suffix
         *
         * @return bool|int
         */
        $hasSuffix = function ($string, $suffix) {
            return false !== strpos($string, $suffix, -0);
        };

        // Magic call to every{frequency} methods
        if ($hasPrefix($name, $everyPrefix)) {
            // Handle every{Frequency}() magic methods
            $frequencyConstant = $getConstantName(
                self::class,
                sprintf(
                    'FREQUENCY_%s',
                    strtoupper(substr($name, strlen($everyPrefix)))
                )
            );

            if (defined($frequencyConstant)) {
                return $this->setFrequency(constant($frequencyConstant));
            }
        } elseif ($hasPrefix($name, $ofEveryPrefix) && false !== $hasSuffix($name, $monthSuffix)) {
            // Month setter
            // get ofEvery{$1}Month
            $ofEveryPrefixLength = strlen($ofEveryPrefix);
            $monthSuffixLength = strlen($monthSuffix);
            $monthNumericRepresentation = substr(
                $name,
                $ofEveryPrefixLength,
                strlen($name) - $monthSuffixLength - $ofEveryPrefixLength
            );

            // Wrap in try catch to handle invalid month numbers as bad method calls
            try {
                if ($monthNumericRepresentation === 'Other') {
                    $monthNumber = 2;
                } else {
                    $monthNumber = (int) $monthNumericRepresentation;

                    // Check if the passed number+ordinal is the correct ordinal for the number
                    $formatter = new NumberFormatter('en-GB', NumberFormatter::ORDINAL);
                    $ordinalMonthNumber = $formatter->format($monthNumber);

                    if ($ordinalMonthNumber !== $monthNumericRepresentation) {
                        throw new InvalidArgumentException('Invalid number ordinal');
                    }
                }

                return $this->setMonths($monthNumber);
            } catch (InvalidArgumentException $e) {
                // Catch, don't throw allow to fall through to bad method call
            }
        } else {
            // Days setter
            $daysToSet = [];
            $dayConstantName = $name;

            // Handle and{day}() magic methods
            //  Defaults will handle the {day}() magic methods
            if (false !== strpos($name, $andPrefix, 0)) {
                $daysToSet = $this->getDays();
                $dayConstantName = substr($name, strlen($andPrefix));
            }

            $dayConstantName = $getConstantName(
                IntervalInterface::class,
                strtoupper($dayConstantName)
            );

            if (defined($dayConstantName)) {
                array_push($daysToSet, constant($dayConstantName));
            }

            if (!empty($daysToSet)) {
                return $this->setDays(array_unique($daysToSet));
            }
        }

        throw new BadMethodCallException('Call to undefined method {' . __CLASS__ . '}::{' . $name . '}()');
    }
}
