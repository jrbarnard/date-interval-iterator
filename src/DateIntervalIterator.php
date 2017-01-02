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
     * @var IntervalInterface
     */
    protected $interval;

    /**
     * When to start the Iterator from
     * @var DateTime
     */
    protected $start;

    /**
     * When to end the iterator after, number of occurrences or Carbon instance
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
     * This can be an int between 1 and 100 or a carbon instance / datetime string
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

        // convert to a Carbon instance (handles datetime strings)
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
            'You must pass a valid endAfter datetime string, Carbon instance or int within the valid occurrences range'
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
        // TODO: Implement current() method.
    }

    /**
     * Move forward to next element
     * @link http://php.net/manual/en/iterator.next.php
     * @return void Any returned value is ignored.
     * @since 5.0.0
     */
    public function next()
    {
        // TODO: Implement next() method.
    }

    /**
     * Return the key of the current element
     * @link http://php.net/manual/en/iterator.key.php
     * @return mixed scalar on success, or null on failure.
     * @since 5.0.0
     */
    public function key()
    {
        // TODO: Implement key() method.
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
        // TODO: Implement valid() method.
    }

    /**
     * Rewind the Iterator to the first element
     * @link http://php.net/manual/en/iterator.rewind.php
     * @return void Any returned value is ignored.
     * @since 5.0.0
     */
    public function rewind()
    {
        // TODO: Implement rewind() method.
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
        // TODO: Implement count() method.
    }
}