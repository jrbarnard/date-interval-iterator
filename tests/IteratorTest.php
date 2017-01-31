<?php

use DateIntervalIterator\DateIntervalIterator;
use DateIntervalIterator\Intervals\IntervalInterface;
use DateIntervalIterator\Exceptions\InvalidArgumentException;

/**
 * Class IteratorTest
 */
class IteratorTest extends PHPUnit_Framework_TestCase
{
    /**
     * Extracted to a const just in case is changed during development
     */
    const ITERATOR_CLASS = DateIntervalIterator::class;

    // Tests:
    // - constructor will take start, interval and end and pass off to relevant methods - done
    // - setStart expects a start date, either datetime, string or timestamp - done
    // - setStart will return iterator - done
    // - setStart will throw exception if given invalid start parameter - done
    // - setInterval accepts parameter that must implement IntervalInterface
    // - setEndAfter should accept int for number of occurrences, string or date time
    //  - If int should be within $maxOccurrences
    //  - If string / date time should be after the start date time
    // - setEndAfter, setStart and setInterval should return Iterator
    // - getInterval will return the set interval (IntervalInterface)
    // - getStart will return start datetime (DateTime)
    // - getEndAfter will return end after set (int or datetime)
    // - getMaxOccurrences will return set max occurrences
    // - setMaxOccurrences must be greater than 0 (throws exception otherwise), will return Iterator, and will set the occurrences
    // - init is just a pass through to the constructor and will return iterator
    // - isValidOccurrences will return false if non int passed or int out of range of 1 - max, true if in range
    //
    // ITERATOR SPECIFIC TESTS
    // - looping, getting etc

    /** @test */
    public function setStart_will_return_iterator()
    {
        $iterator = $this->generateIterator();
        $result = $iterator->setStart(new DateTime());
        $this->assertInstanceOf(self::ITERATOR_CLASS, $result);
    }

    /**
     * @dataProvider invalidSetStartOptions
     * @test
     */
    public function setStart_will_throw_exception_if_passed_invalid_argument($start)
    {
        $iterator = $this->generateIterator();

        $this->expectException(InvalidArgumentException::class);
        $iterator->setStart($start);
    }

    /** @test */
    public function setStart_will_accept_valid_date_time_instance_date_string_or_timestamp()
    {
        // Our test values
        $validDateTime = new DateTime();
        $timestamp = (new DateTime('2012-12-12'))->getTimestamp();
        $dateTimeString = '2016-12-12';

        // Check they are the types we want to check for
        $this->assertTrue(is_string($dateTimeString));
        $this->assertTrue(is_int($timestamp));
        $this->assertInstanceOf(DateTime::class, $validDateTime);

        // Initialise the iterator
        $originalTimestamp = strtotime('2016-12-12');
        $iterator = $this->generateIterator($originalTimestamp);

        $this->assertInstanceOf(self::ITERATOR_CLASS, $iterator);

        // Ensure the original passed in and what we will be setting aren't the same, otherwise we may get a false positive
        $this->assertNotSame($iterator->getStart()->getTimestamp(), $validDateTime->getTimestamp());

        // Now set using a standard datetime instance
        $iterator->setStart($validDateTime);
        $this->assertInstanceOf(DateTime::class, $iterator->getStart());
        $this->assertSame($validDateTime->getTimestamp(), $iterator->getStart()->getTimestamp());

        // Again check not same as what we will be setting
        $this->assertNotSame($iterator->getStart()->getTimestamp(), $timestamp);

        $iterator->setStart($timestamp);
        $this->assertInstanceOf(DateTime::class, $iterator->getStart());
        $this->assertSame($timestamp, $iterator->getStart()->getTimestamp());

        // Again check not same as what we will be setting
        $this->assertNotSame($iterator->getStart()->getTimestamp(), strtotime($dateTimeString));

        $iterator->setStart($dateTimeString);
        $this->assertInstanceOf(DateTime::class, $iterator->getStart());
        $this->assertSame(strtotime($dateTimeString), $iterator->getStart()->getTimestamp());
    }

    /** @test */
    public function iterator_takes_start_interval_and_end_after_in_constructor()
    {
        $interval = new TestInterval();
        $dateTime = new DateTime();
        $endAfter = 10;

        $classname = self::ITERATOR_CLASS;

        // Get mock, without the constructor being called
        $mock = $this->getMockBuilder($classname)
            ->disableOriginalConstructor()
            ->setMethods([
                'setStart',
                'setInterval',
                'setEndAfter'
            ])
            ->getMockForAbstractClass();

        // set expectations for constructor calls
        $mock->expects($this->once())
            ->method('setStart')
            ->with($dateTime);

        $mock->expects($this->once())
            ->method('setInterval')
            ->with($interval);

        $mock->expects($this->once())
            ->method('setEndAfter')
            ->with($endAfter);

        // now call the constructor
        $reflectedClass = new ReflectionClass($classname);
        $constructor = $reflectedClass->getConstructor();
        $constructor->invoke($mock, $dateTime, $interval, $endAfter);
    }

    /**
     * HELPERS / PROVIDERS
     */

    /**
     * @return array
     */
    public function invalidSetStartOptions()
    {
        return [
            [null],
            [[]],
            [['test']],
            [new stdClass],
            [true],
            [false]
        ];
    }

    /**
     * @param null $start
     * @param null $interval
     * @param null $endAfter
     *
     * @return DateIntervalIterator
     */
    protected function generateIterator($start = null, $interval = null, $endAfter = null)
    {
        if (!$interval instanceof IntervalInterface) {
            $interval = new TestInterval();
        }

        if (is_null($start)) {
            $start = new DateTime();
        }

        if (is_null($endAfter)) {
            $endAfter = 10;
        }

        $class = self::ITERATOR_CLASS;

        return new $class($start, $interval, $endAfter);
    }
}

/**
 * Class TestInterval
 */
class TestInterval implements IntervalInterface
{

    /**
     * Method that finds the next occurrence of the interval from current
     *
     * @param DateTime $current
     * @param DateIntervalIterator $iterator
     *
     * @return mixed
     */
    public function findNextOccurrence(DateTime $current, DateIntervalIterator $iterator)
    {
        return $current;
    }
}