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
    // - setInterval accepts parameter that must implement IntervalInterface - done
    // - setInterval will fail if passed non implementation of interval interface - done
    // - setInterval will return iterator - done
    // - setEndAfter should accept int for number of occurrences, string or date time - done
    //  - If int should be within $maxOccurrences, will throw if not - done
    //  - If string / date time should be after the start date time, will throw if not - done
    // - setEndAfter will return Iterator - done
    // - getInterval will return the set interval (IntervalInterface) - done
    // - getStart will return start datetime (DateTime) - done
    // - getEndAfter will return end after set (int or datetime) - done
    // - getMaxOccurrences will return set max occurrences - done
    // - setMaxOccurrences must be greater than 0 (throws exception otherwise), will return Iterator, and will set the occurrences - done
    // - init is just a pass through to the constructor and will return iterator - done
    // - isValidOccurrences will return false if non int passed or int out of range of 1 - max, true if in range - done
    //
    // ITERATOR SPECIFIC TESTS
    // - looping, getting etc

    /**
     * @dataProvider invalidMaxOccurrences
     * @test
     * @param $occurrences
     */
    public function isValidOccurrences_will_return_false_if_non_int_passed_or_int_out_of_max_range($occurrences)
    {
        $iterator = $this->generateIterator();

        $this->assertFalse($iterator->isValidOccurrences($occurrences));
    }

    /** @test */
    public function init_will_return_new_iterator()
    {
        $datetime = new DateTime();
        $interval = new TestInterval();
        $endAfter = 10;
        $iterator = call_user_func([self::ITERATOR_CLASS,'init'], $datetime, $interval, $endAfter);
        $this->assertInstanceOf(self::ITERATOR_CLASS, $iterator);
    }

    /**
     * @dataProvider invalidMaxOccurrences
     * @test
     * @param $occurrences
     */
    public function setMaxOccurrences_will_throw_if_invalid_number_of_occurrences($occurrences)
    {
        $iterator = $this->generateIterator();

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('You must pass a max occurrences of more than 0');
        $iterator->setMaxOccurrences($occurrences);
    }

    /** @test */
    public function setMaxOccurrences_will_return_iterator()
    {
        $iterator = $this->generateIterator();

        $result = $iterator->setMaxOccurrences(100);
        $this->assertInstanceOf(self::ITERATOR_CLASS, $result);
    }

    /** @test */
    public function getMaxOccurrences_will_return_the_number_of_max_occurrences_set()
    {
        $iterator = $this->generateIterator();

        $maxOccurrences = 100;
        $iterator->setMaxOccurrences(100);

        $this->assertTrue(is_int($iterator->getMaxOccurrences()));
        $this->assertSame($maxOccurrences, $iterator->getMaxOccurrences());
    }

    /** @test */
    public function getStart_will_return_set_start_datetime()
    {
        // Try for timestamp, string and datetime instance
        $iterator = $this->generateIterator();

        $datetime = new DateTime('2016-12-12');
        $this->assertNotEquals($datetime, $iterator->getStart());
        $iterator->setStart($datetime);
        $this->assertInstanceOf(DateTime::class, $iterator->getStart());
        $this->assertSame($datetime, $iterator->getStart()); // references too

        // Try with string
        $datetimeString = '2012-12-12';
        $this->assertNotEquals(new DateTime($datetimeString), $iterator->getStart());
        $iterator->setStart($datetimeString);
        $this->assertInstanceOf(DateTime::class, $iterator->getStart());
        $this->assertEquals(new DateTime($datetimeString), $iterator->getStart());

        // Try with timestamp
        $timestamp = strtotime('2009-12-12');
        $this->assertNotEquals((new DateTime())->setTimestamp($timestamp), $iterator->getStart());
        $iterator->setStart($timestamp);
        $this->assertInstanceOf(DateTime::class, $iterator->getStart());
        $this->assertEquals((new DateTime())->setTimestamp($timestamp), $iterator->getStart());
    }

    /** @test */
    public function getInterval_will_return_set_interval()
    {
        $iterator = $this->generateIterator();

        $newInterval = new SetIntervalTestInterval();
        $this->assertNotSame($newInterval, $iterator->getInterval());
        $iterator->setInterval($newInterval);

        $this->assertInstanceOf(SetIntervalTestInterval::class, $iterator->getInterval());
        $this->assertSame($newInterval, $iterator->getInterval());
    }

    /** @test */
    public function getEndAfter_will_return_the_set_end_after()
    {
        $startDateTime = new DateTime();
        $iterator = $this->generateIterator($startDateTime);

        // Start with int
        $endAfter = 20;
        $this->assertNotSame($endAfter, $iterator->getEndAfter());
        $iterator->setEndAfter($endAfter);

        $this->assertTrue(is_int($iterator->getEndAfter()));
        $this->assertSame($endAfter, $iterator->getEndAfter());

        // Try with datetime
        $endAfter = (clone $startDateTime)->add(new DateInterval('P10D'));
        $this->assertNotSame($endAfter, $iterator->getEndAfter());
        $iterator->setEndAfter($endAfter);

        $this->assertInstanceOf(DateTime::class, $iterator->getEndAfter());
        $this->assertSame($endAfter, $iterator->getEndAfter()); // referencing same object

        // Try with datetime string, check converted to datetime
        $format = 'Y-m-d H:i:s';
        $endAfter = (clone $endAfter)->add(new DateInterval('P10D'))->format($format);
        $this->assertNotSame($endAfter, $iterator->getEndAfter()->format($format));
        $iterator->setEndAfter($endAfter);

        $this->assertInstanceOf(DateTime::class, $iterator->getEndAfter());
        $this->assertEquals(new DateTime($endAfter), $iterator->getEndAfter());
    }

    /** @test */
    public function setEndAfter_will_throw_if_date_time_is_before_the_start_date()
    {
        $startDateTime = new DateTime();
        $iterator = $this->generateIterator($startDateTime);

        // Create a past date time and verify it throws if we attempt to set it
        $pastDateTime = (clone $startDateTime)->sub(new DateInterval('P10D'));
        $this->assertLessThan($startDateTime->getTimestamp(), $pastDateTime->getTimestamp());

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('You must pass an end datetime that is greater than the start date time');
        $iterator->setEndAfter($pastDateTime);
    }

    /**
     * @dataProvider invalidEndAfters
     * @test
     *
     * @param $endAfter
     */
    public function setEndAfter_will_throw_if_invalid_input($endAfter)
    {
        $iterator = $this->generateIterator();

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage(
            'You must pass a valid endAfter datetime string, DateTime instance or int within the valid occurrences range'
        );
        $iterator->setEndAfter($endAfter);
    }

    /** @test */
    public function setEndAfter_accepts_datetime_or_datetime_string()
    {
        $iterator = $this->generateIterator();

        // Ensure in future from original start
        $datetime = (clone $iterator->getStart())->add(new DateInterval('P10D'));
        $this->assertNotSame($datetime->getTimestamp(), $iterator->getStart());
        // Check not same for false positives
        $originalEndAfter = $iterator->getEndAfter();
        $originalEndAfterTimeStamp = $originalEndAfter instanceof DateTime ? $originalEndAfter->getTimestamp() : $originalEndAfter;
        $this->assertNotSame($datetime->getTimestamp(), $originalEndAfterTimeStamp);

        // Now set using date time instance
        $this->assertInstanceOf(DateTime::class, $datetime);
        $iterator->setEndAfter($datetime);
        $this->assertSame($datetime->getTimestamp(), $iterator->getEndAfter()->getTimestamp());

        // Now try with datetime string
        $datetimeString = $datetime->add(new DateInterval('P10D'))->format('Y-m-d');
        // Check not same for false positives
        $this->assertNotSame(strtotime($datetimeString), $iterator->getEndAfter()->getTimestamp());

        // Set and check
        $iterator->setEndAfter($datetimeString);
        $this->assertSame(strtotime($datetimeString), $iterator->getEndAfter()->getTimestamp());
    }

    /** @test */
    public function setEndAfter_will_return_iterator()
    {
        $iterator = $this->generateIterator();
        $result = $iterator->setEndAfter(10);
        $this->assertInstanceOf(self::ITERATOR_CLASS, $result);
    }

    /**
     * @dataProvider invalidIntEndAfters
     * @test
     *
     * @param $endAfter
     */
    public function setEndAfter_will_throw_if_int_is_out_of_range($endAfter)
    {
        $iterator = $this->generateIterator();

        // Set max to a low number
        $iterator->setMaxOccurrences(50);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage(
            'You must pass a valid endAfter datetime string, DateTime instance or int within the valid occurrences range'
        );
        $iterator->setEndAfter($endAfter);
    }

    /** @test */
    public function setEndAfter_will_accept_int_number_of_occurrences_within_range()
    {
        $iterator = $this->generateIterator();

        $endAfter = 12;

        // Check not already set as we will get a false positive
        $this->assertNotSame(12, $iterator->getEndAfter());

        // Set and check set properly
        $iterator->setEndAfter($endAfter);
        $this->assertSame($endAfter, $iterator->getEndAfter());
    }

    /**
     * @dataProvider invalidIntervals
     * @test
     *
     * @param $interval
     */
    public function setInterval_will_throw_if_passed_non_valid_implementation($interval)
    {
        $iterator = $this->generateIterator();

        $this->expectException(TypeError::class);
        $iterator->setInterval($interval);
    }

    /** @test */
    public function setInterval_will_return_iterator()
    {
        $iterator = $this->generateIterator();
        $result = $iterator->setInterval(new SetIntervalTestInterval());
        $this->assertInstanceOf(self::ITERATOR_CLASS, $result);
    }

    /** @test */
    public function setInterval_accepts_implementation_of_interval_interface()
    {
        $iterator = $this->generateIterator();

        // Check not set previously
        $this->assertNotInstanceOf(SetIntervalTestInterval::class, $iterator->getInterval());

        $iterator->setInterval(new SetIntervalTestInterval());
        $this->assertInstanceOf(SetIntervalTestInterval::class, $iterator->getInterval());
    }

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
     *
     * @param $start
     */
    public function setStart_will_throw_exception_if_passed_invalid_argument($start)
    {
        $iterator = $this->generateIterator();

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage(
            'Pass a valid DateTime instance, timestamp or date time string'
        );
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
    public function invalidMaxOccurrences()
    {
        return [
            [new stdClass],
            [[]],
            [['test']],
            [[1,2,3]],
            [true],
            [false],
            ['test'],
            [0],
            [-1000],
            [-1]
        ];
    }

    /**
     * @return array
     */
    public function invalidEndAfters()
    {
        return [
            [new stdClass],
            [[]],
            [['test']],
            [[1,2,3]],
            [true],
            [false],
            ['test']
        ];
    }

    /**
     * @return array
     */
    public function invalidIntEndAfters()
    {
        return [
            [-1],
            [0],
            [-999999],
            [51],
            [100],
            [99999999]
        ];
    }

    /**
     * @return array
     */
    public function invalidIntervals()
    {
        return [
            [new stdClass],
            [[]],
            [['test']],
            [1,2,3],
            [true],
            [false],
            [1],
            [99999999],
            ['test'],
            [''],
            [new DateTime()]
        ];
    }

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

/**
 * Class SetIntervalTestInterval
 * For use by: setInterval_accepts_implementation_of_interval_interface
 */
class SetIntervalTestInterval implements IntervalInterface
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