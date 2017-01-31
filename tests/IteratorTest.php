<?php
use DateIntervalIterator\DateIntervalIterator;

/**
 * Class IteratorTest
 */
class IteratorTest extends PHPUnit_Framework_TestCase
{
    // Tests:
    // - constructor first argument expects a start date, either datetime, string or timestamp
    //  - Same as setStart method
    // - Second parameter must implement the IntervalInterface
    // - Third parameter (setEndAfter) should be int for number of occurrences, string or date time
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
    public function iterator_takes_start_interval_and_end_after_in_constructor()
    {
        $interval = new TestInterval();
        $dateTime = new DateTime();
        $endAfter = 10;

        $classname = DateIntervalIterator::class;

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
}

/**
 * Class TestInterval
 */
class TestInterval implements \DateIntervalIterator\Intervals\IntervalInterface
{

    /**
     * Method that finds the next occurrence of the interval from current
     *
     * @param DateTime $current
     * @param \DateIntervalIterator\DateIntervalIterator $iterator
     *
     * @return mixed
     */
    public function findNextOccurrence(DateTime $current, \DateIntervalIterator\DateIntervalIterator $iterator)
    {
        return $current;
    }
}