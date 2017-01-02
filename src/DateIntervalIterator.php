<?php
namespace DateIntervalIterator;

use DateIntervalIterator\Exceptions\InvalidArgumentException;
use DateTime;
use DateIntervalIterator\Intervals\IntervalInterface;

/**
 * Class DateIntervalIterator
 * This class allows you to generate a time period with a specified interval that you can then iterate over.
 * For instance you can choose to get the 2nd Mondays, Wednesdays and Fridays of each month from now up to 100 occurrences.
 * @package DateIntervalIterator
 */
class DateIntervalIterator implements \Iterator, \Countable
{
    /**
     * The format we want the date times as
     */
    const DATE_TIME_FORMAT = 'Y-m-d H:i:s';

    /**
     * @var IntervalInterface
     */
    protected $interval;

    /**
     * When to start the Iterator from
     * @var DateTime
     */
    protected $start;

    /**
     * When to end the iterator after, number of occurrences or DateTime instance
     * @var int|DateTime
     */
    protected $end;

    /**
     * The max number of occurrences a repetition can be set to
     * @var int
     */
    protected $maxOccurrences = 100;

    /**
     * All the occurrences, built during iteration / count
     * @var Occurrences
     */
    protected $occurrences;

    /**
     * The current occurrence key in the iterator
     * @var int
     */
    protected $currentOccurrenceKey = null;

    /**
     * @var int
     */
    protected $occurrenceCount = 0;

    /**
     * @var array
     */
    protected $skip = [];

    /**
     * DateTimeIterator constructor.
     * @param $start
     * @param IntervalInterface $interval
     * @param $end
     */
    public function __construct($start, IntervalInterface $interval, $end)
    {
        $this->setStart($start);
        $this->setInterval($interval);
        $this->setEndAfter($end);
    }

    /**
     * @param $start
     * @param $interval
     * @param $end
     * @return DateIntervalIterator
     */
    public static function init($start, $interval, $end)
    {
        return new self($start, $interval, $end);
    }

    /**
     * @return DateTime
     */
    public function getStart()
    {
        return $this->start;
    }

    /**
     * @param $start
     * @return $this
     */
    public function setStart($start)
    {
        if (!$start instanceof DateTime) {
            if (!is_int($start)) {
                $start = strtotime($start);
            }
            $start = new DateTime($start);
        }

        $this->start = $start;
        return $this;
    }

    /**
     * Check whether the occurrences int passed in is within a range
     * @param $occurrences
     * @return bool
     */
    public function isValidOccurrences($occurrences)
    {
        if ($occurrences < 1 || $occurrences > $this->getMaxOccurrences()) {
            return false;
        }

        return true;
    }

    /**
     * @return int
     */
    public function getMaxOccurrences()
    {
        return $this->maxOccurrences;
    }

    /**
     * Set the end after
     * This can be an int between 1 and 100 or a DateTime instance / datetime string
     * @param int|DateTime $endAfter
     * @return $this
     * @throws InvalidArgumentException
     */
    public function setEndAfter($endAfter)
    {
        // if is int, check if valid occurrences int, in which case we will set as int
        if (is_numeric($endAfter) && $this->isValidOccurrences($endAfter)) {
            $this->end = (int) $endAfter;
            return $this;
        }

        // convert to a DateTime instance (handles datetime strings)
        if (is_string($endAfter)) {
            $endAfter = new DateTime(strtotime($endAfter));
        }

        if ($endAfter instanceof DateTime) {
            if ($endAfter->getTimestamp() > $this->getStart()->getTimestamp()) {
                $this->end = $endAfter;
                return $this;
            } else {
                throw new InvalidArgumentException(
                    'You must pass an end datetime that is greater than the start date time'
                );
            }
        }

        throw new InvalidArgumentException(
            'You must pass a valid endAfter datetime string, DateTime instance or int within the valid occurrences range'
        );
    }

    /**
     * @return DateTime|int
     */
    public function getEndAfter()
    {
        return $this->end;
    }

    /**
     * Set the max number of occurrences the repetition can have
     * @param int $occurrences
     * @return $this
     * @throws InvalidArgumentException
     */
    public function setMaxOccurrences($occurrences)
    {
        if ($occurrences < 1) {
            throw new InvalidArgumentException('You must pass a max occurrences of more than 0');
        }

        $this->maxOccurrences = $occurrences;
        return $this;
    }

    /**
     * @param IntervalInterface $interval
     * @return $this
     * @throws InvalidArgumentException
     */
    public function setInterval(IntervalInterface $interval)
    {
        $this->interval = $interval;
        return $this;
    }

    /**
     * @return IntervalInterface
     */
    public function getInterval()
    {
        return $this->interval;
    }

    /**
     * Return the current element
     * @link http://php.net/manual/en/iterator.current.php
     * @return mixed Can return any type.
     * @since 5.0.0
     */
    public function current()
    {
        return $this->occurrences[$this->key()];
    }

    /**
     * Move forward to next element
     * @link http://php.net/manual/en/iterator.next.php
     * @return void Any returned value is ignored.
     * @since 5.0.0
     */
    public function next()
    {
        $this->ifStartGetFirst();

        // if we don't have the next occurrence stored, generate
        if (!$this->occurrenceAlreadySet($this->key() + 1)) {
            $current = $this->current();
            $this->getNextOccurrence($current);
        }

        // up the count
        $this->currentOccurrenceKey++;
    }

    /**
     * Return the key of the current element
     * @link http://php.net/manual/en/iterator.key.php
     * @return mixed scalar on success, or null on failure.
     * @since 5.0.0
     */
    public function key()
    {
        return $this->currentOccurrenceKey;
    }

    /**
     * Checks if current position is valid
     * @link http://php.net/manual/en/iterator.valid.php
     * @return boolean The return value will be casted to boolean and then evaluated.
     * Returns true on success or false on failure.
     * @since 5.0.0
     */
    public function valid()
    {
        if ($this->occurrenceCount > $this->getMaxOccurrences()) {
            return false;
        }

        if (!$this->occurrenceAlreadySet($this->key())) {
            return false;
        }

        return true;
    }

    /**
     * Rewind the Iterator to the first element
     * @link http://php.net/manual/en/iterator.rewind.php
     * @return void Any returned value is ignored.
     * @since 5.0.0
     */
    public function rewind()
    {
        $this->ifStartGetFirst();

        $this->currentOccurrenceKey = 0;
    }

    /**
     * Count elements of an object
     * @link http://php.net/manual/en/countable.count.php
     * @return int The custom count as an integer.
     * </p>
     * <p>
     * The return value is cast to an integer.
     * @since 5.1.0
     */
    public function count()
    {
        if ($this->occurrenceCount === 0) {
            foreach($this as $occurrence) {
                // do nothing, just iterating all occurrences for count
            }
        }

        return $this->occurrenceCount;
    }

    /**
     * Checks to see if an occurrence by key is already set
     * @param $occurrence
     * @return bool
     */
    public function occurrenceAlreadySet($occurrence)
    {
        if (!isset($this->occurrences[$occurrence])) {
            return false;
        }

        return true;
    }

    /**
     * Add a new occurrence to the array and up the occurrence count
     * @param DateTime $occurrence
     * @return $this
     */
    protected function addOccurrence(DateTime $occurrence)
    {
        $this->occurrences[] = $occurrence;
        $this->occurrenceCount++;

        return $this;
    }

    /**
     * @param int $key
     * @return mixed
     */
    protected function getOccurrence($key)
    {
        return $this->occurrences[$key];
    }

    /**
     * if we are at the start go to the first occurrence
     * @return DateTime
     */
    protected function ifStartGetFirst()
    {
        if (is_null($this->key())) {
            return $this->getFirstOccurrence();
        }
    }

    /**
     * Pass through to help us get the first occurrence within the occurrences
     * @return DateTime
     */
    protected function getFirstOccurrence()
    {
        if ($this->occurrenceAlreadySet(0)) {
            $firstOccurrence =  $this->getOccurrence(0);
        } else {
            $firstOccurrence = $this->getNextOccurrence($this->getStart());
        }
        return $firstOccurrence;
    }

    /**
     * Gets the next occurrence within the iterator
     * @param DateTime $current
     * @return DateTime|bool
     * @throws InvalidArgumentException
     */
    public function getNextOccurrence(DateTime $current)
    {
        // Validate the specific requirements of each interval
        $interval = $this->getInterval();

        $occurrence = $interval->findNextOccurrence($current, $this);

        // Ensure that the next occurrence is not out of our start -> end period
        if (!$this->isWithinPeriod($occurrence)) {
            return false;
        }

        $this->addOccurrence($occurrence);

        return $occurrence;
    }

    /**
     * Check whether an occurrence is within our start -> end period
     * @param DateTime $occurrence
     * @return bool
     */
    protected function isWithinPeriod(DateTime $occurrence)
    {
        $endAfter = $this->getEndAfter();

        // is not valid if we have hit the set occurrences
        if (is_int($endAfter) && $this->occurrenceCount >= $endAfter) {
            return false;
        }

        // Check that the time of the next occurrence isn't beyond our endAfter datetime (if is a datetime)
        if ($endAfter instanceof DateTime && $occurrence->getTimestamp() > $endAfter->getTimestamp()) {
            return false;
        }

        return true;
    }

    /**
     * Add a datetime to skip within the iterator
     * @param $skip DateTime|string|array
     * @return $this
     */
    public function skip($skip)
    {
        if (!is_array($skip)) {
            $skip = [$skip];
        }

        foreach($skip as $occurrence) {
            if (!$occurrence instanceof DateTime) {
                $occurrence = new DateTime(strtotime($occurrence));
            }

            if ($this->shouldSkip($occurrence)) {
                continue;
            }

            $this->skip[] = $occurrence->format(self::DATE_TIME_FORMAT);
        }

        return $this;
    }

    /**
     * @param $occurrence
     * @return bool
     */
    public function shouldSkip($occurrence)
    {
        if ($occurrence instanceof DateTime) {
            $occurrence = $occurrence->format(self::DATE_TIME_FORMAT);
        }

        if (!in_array($occurrence, $this->skip)) {
            return false;
        }

        return true;
    }
}