<?php
namespace DateIntervalIterator;

use DateIntervalIterator\Exceptions\InvalidArgumentException;
use DateTime;

/**
 * Class Occurrences
 * A wrapper to store the found occurrences in
 * @package DateIntervalIterator
 */
class Occurrences implements \ArrayAccess, \Countable
{
    /**
     * @var array
     */
    protected $occurrences = [];

    /**
     * Occurrences constructor.
     *
     * @param array $occurrences
     */
    public function __construct(array $occurrences = [])
    {
        $this->setOccurrences($occurrences);
    }

    /**
     * Whether a offset exists
     * @link http://php.net/manual/en/arrayaccess.offsetexists.php
     *
     * @param mixed $offset <p>
     * An offset to check for.
     * </p>
     *
     * @return boolean true on success or false on failure.
     * </p>
     * <p>
     * The return value will be casted to boolean if non-boolean was returned.
     * @since 5.0.0
     */
    public function offsetExists($offset)
    {
        if (!isset($this->getOccurrences()[$offset])) {
            return false;
        }

        return true;
    }

    /**
     * Offset to retrieve
     * @link http://php.net/manual/en/arrayaccess.offsetget.php
     *
     * @param mixed $offset <p>
     * The offset to retrieve.
     * </p>
     *
     * @return mixed Can return all value types.
     * @since 5.0.0
     */
    public function offsetGet($offset)
    {
        return $this->getOccurrences()[$offset];
    }

    /**
     * Offset to set
     * @link http://php.net/manual/en/arrayaccess.offsetset.php
     *
     * @param mixed $offset <p>
     * The offset to assign the value to.
     * </p>
     * @param mixed $value <p>
     * The value to set.
     * </p>
     *
     * @return void
     * @since 5.0.0
     */
    public function offsetSet($offset, $value)
    {
        if (!$value instanceof DateTime) {
            throw new InvalidArgumentException(
                'You must pass a valid DateTime instance through as an occurrence'
            );
        }

        if (is_null($offset)) {
            $this->occurrences[] = $value;
        } else {
            $this->occurrences[$offset] = $value;
        }
    }

    /**
     * Offset to unset
     * @link http://php.net/manual/en/arrayaccess.offsetunset.php
     *
     * @param mixed $offset <p>
     * The offset to unset.
     * </p>
     *
     * @return void
     * @since 5.0.0
     */
    public function offsetUnset($offset)
    {
        unset($this->occurrences[$offset]);
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
        return count($this->getOccurrences());
    }

    /**
     * @return array
     */
    public function toArray()
    {
        return $this->getOccurrences();
    }

    /**
     * @return array
     */
    public function getOccurrences()
    {
        return $this->occurrences;
    }

    /**
     * @param array $occurrences
     */
    public function setOccurrences(array $occurrences)
    {
        $this->occurrences = $occurrences;
    }

    /**
     * @param DateTime $occurrence
     *
     * @return $this
     */
    public function push($occurrence)
    {
        $this[] = $occurrence;

        return $this;
    }

    /**
     * @param $offset
     *
     * @return mixed
     */
    public function getOccurrence($offset)
    {
        return $this->offsetGet($offset);
    }

    /**
     * @return DateTime
     */
    public function pop()
    {
        return array_pop($this->occurrences);
    }
}