<?php
namespace JRBarnard\RecurrenceTests;

use stdClass;
use DateTime;
use TypeError;
use DateInterval;
use ReflectionClass;
use ReflectionObject;
use JRBarnard\Recurrence\Occurrences;
use JRBarnard\Recurrence\Intervals\IntervalInterface;
use JRBarnard\Recurrence\Exceptions\InvalidArgumentException;

/**
 * Class IteratorTest
 */
class IteratorTest extends TestCase
{
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
    // - current will return the current point within the iterator - done
    // - key will point to the current key within the iterator - done
    // - count will return the total number of occurrences - done
    // - valid will return false if the total number of occurrences is greater than max occurrences or if the key is already set - done
    // - iteration will cache within iterator after running - done
    // - iteration will cache in parts in iterator not run all the way through - done
    // - total number of occurrences will never exceed max occurrences, whether end after is set to an int or datetime - done
    // - iterator will stop once reached datetime of end after, max occurrences or number of occurrences - done
    // - Can up max occurrences after initial iteration and will continue to get additional occurrences - done
    // - Can set date time strings, date time instances to skip and will skip when iteration - done
    // - Can check if a datetime string or datetime should be skipped - done
    // - Wont add to skip if already skipping - done
    // - Can get occurrence by key using method - done
    // - Get first occurrence will get already set if set already, otherwise will get next - done
    // - Can set direction, will return self - done
    // - Cant set an invalid direction - done
    // - Can get direction, will return set - done
    // - If set direction backwards, will run iterator backwards - done
    // - If running backwards and set endAfter to datetime, will run to that datetime as does forward - done
    // - Can get occurrences, will return occurrences object - done
    // - Can get occurrences even if not iterated, will populate and return occurrences - done
    // - BUGFIX: setting max occurrences lower after initial iteration will stop iteration from working
    // - Will take into account time
    // - Will wrap months
    // - getNextOccurrence
    // - isWithinPeriod
    // - next

    /** @test */
    public function if_running_backwards_with_date_endafter_will_run_up_till_it()
    {
        $start = new DateTime('2012-10-10');
        $endAfter = new DateTime('2012-09-19');
        $interval = new OneDayInterval();
        $iterator = $this->generateIterator($start, $interval, $endAfter, IntervalInterface::BACKWARDS);

        $expected = $start;
        foreach($iterator as $occurrence) {
            $cloned = clone $expected;
            $expected = $cloned->sub(new DateInterval('P1D'));
            $this->assertEquals($expected->getTimestamp(), $occurrence->getTimestamp());
        }

        $this->assertEquals(21, count($iterator));
    }

    /** @test */
    public function can_get_occurrences_before_iterating_the_iterator_and_will_internally_run_to_populate_before_returning()
    {
        $start = new DateTime('2012-10-10');
        $interval = new OneDayInterval();
        $endAfter = 10;
        $iterator = $this->generateIterator($start, $interval, $endAfter);

        // DONT RUN THE ITERATOR

        $occurrences = $iterator->getOccurrences();

        $expected = $start;
        foreach($occurrences as $occurrence) {
            $cloned = clone $start;
            $expected = $cloned->add(new DateInterval('P1D'));
            $this->assertInstanceOf(DateTime::class, $occurrence);
            $this->assertEquals($expected->getTimestamp(), $occurrence->getTimestamp());
        }

        $this->assertCount($endAfter, $occurrences);
    }

    /** @test */
    public function can_get_occurrences_will_include_the_occurrences_from_the_iterator()
    {
        $start = new DateTime('2012-10-10');
        $interval = new OneDayInterval();
        $endAfter = 10;
        $iterator = $this->generateIterator($start, $interval, $endAfter);
        count($iterator);

        $occurrences = $iterator->getOccurrences();

        $expected = $start;
        foreach($occurrences as $occurrence) {
            $cloned = clone $start;
            $expected = $cloned->add(new DateInterval('P1D'));
            $this->assertInstanceOf(DateTime::class, $occurrence);
            $this->assertEquals($expected->getTimestamp(), $occurrence->getTimestamp());
        }

        $this->assertCount($endAfter, $occurrences);
    }

    /** @test */
    public function can_get_occurrences_will_return_occurrences_object()
    {
        $iterator = $this->generateIterator();

        $result = $iterator->getOccurrences();

        $this->assertInstanceOf(Occurrences::class, $result);
    }

    /** @test */
    public function if_set_direction_backwards_will_run_iterator_backwards()
    {
        $start = new DateTime('2012-10-10');
        $endAfter = 5;
        $interval = new OneDayInterval();
        $iterator = $this->generateIterator($start, $interval, $endAfter);
        $iterator->setDirection(IntervalInterface::BACKWARDS);

        $expected = $start;
        foreach($iterator as $occurrence) {
            $cloned = clone $expected;
            $expected = $cloned->sub(new DateInterval('P1D'));
            $this->assertEquals($expected->getTimestamp(), $occurrence->getTimestamp());
        }

        $this->assertEquals($endAfter, count($iterator));
    }

    /** @test */
    public function can_get_direction()
    {
        $direction = IntervalInterface::BACKWARDS;

        $iterator = $this->generateIterator();

        $this->assertNotEquals($direction, $iterator->getDirection());
        $iterator->setDirection($direction);

        $this->assertSame($direction, $iterator->getDirection());
    }

    /**
     * @dataProvider invalidDirections
     * @test
     * @param $invalidDirection
     */
    public function cant_set_direction_to_an_invalid_direction($invalidDirection)
    {
        $iterator = $this->generateIterator();

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage(
            'The direction must be one of the valid directions set within the Interval Interface'
        );
        $iterator->setDirection($invalidDirection);
    }

    /** @test */
    public function can_set_direction_will_return_self()
    {
        $iterator = $this->generateIterator();
        $result = $iterator->setDirection(IntervalInterface::FORWARDS);

        $this->assertInstanceOf(self::ITERATOR_CLASS, $result);
    }

    /** @test */
    public function can_get_first_occurrence_will_get_if_already_got()
    {
        $endAfter = 5;
        $start = new DateTime('2014-06-12 12:03:30');
        $interval = new OneDayInterval();

        $iterator = $this->generateIterator($start, $interval, $endAfter);

        count($iterator);

        $mock = $this->getMockBuilder(OneDayInterval::class)
            ->setMethods([
                'findNextOccurrence'
            ])
            ->getMockForAbstractClass();

        $iterator->setInterval($mock);

        $class = new ReflectionObject($iterator);
        $method = $class->getMethod('getFirstOccurrence');
        $method->setAccessible(true);

        // Check we never go into the interval
        $mock->expects($this->never())
            ->method('findNextOccurrence');

        $method->invoke($iterator);
    }

    /** @test */
    public function can_get_first_occurrence_will_get_next_if_not_yet_got()
    {
        $endAfter = 5;
        $start = new DateTime('2014-06-12 12:03:30');

        $mock = $this->getMockBuilder(OneDayInterval::class)
            ->setMethods([
                'findNextOccurrence'
            ])
            ->getMockForAbstractClass();

        $iterator = $this->generateIterator($start, $mock, $endAfter);

        $class = new ReflectionObject($iterator);
        $method = $class->getMethod('getFirstOccurrence');
        $method->setAccessible(true);

        // Not yet iterated, check triggers next occurrence method within interval
        $mock->expects($this->once())
            ->method('findNextOccurrence')
            ->willReturn(new DateTime('2014-06-13 12:03:30'));

        $method->invoke($iterator);
    }

    /** @test */
    public function can_get_occurrence_by_key()
    {
        $endAfter = 5;
        $start = new DateTime('2014-06-12 12:03:30');
        $interval = new OneDayInterval();

        $iterator = $this->generateIterator($start, $interval, $endAfter);

        $class = new ReflectionObject($iterator);
        $method = $class->getMethod('getOccurrence');
        $method->setAccessible(true);

        count($iterator);

        $this->assertEquals('2014-06-14 12:03:30', $method->invoke($iterator, 1)->format(self::DATE_TIME_FORMAT));
    }

    /** @test */
    public function wont_add_to_skip_if_already_skipping()
    {
        $endAfter = 5;
        $start = new DateTime('2014-06-12 12:03:30');
        $interval = new OneDayInterval();

        $iterator = $this->generateIterator($start, $interval, $endAfter);

        $class = new ReflectionObject($iterator);
        $property = $class->getProperty('skip');
        $property->setAccessible(true);

        $this->assertEmpty($property->getValue($iterator));

        $iterator->skip(new DateTime('2014-06-15 12:03:30'));

        $this->assertCount(1, $property->getValue($iterator));

        $iterator->skip(new DateTime('2014-06-15 12:03:30'));

        $this->assertCount(1, $property->getValue($iterator));
    }

    /** @test */
    public function can_check_if_datetime_should_be_skipped()
    {
        $endAfter = 5;
        $start = new DateTime('2014-06-12 12:03:30');
        $interval = new OneDayInterval();

        $iterator = $this->generateIterator($start, $interval, $endAfter);

        $skip = new DateTime('2014-06-15 12:03:30');

        $this->assertFalse($iterator->shouldSkip($skip));
        $this->assertFalse($iterator->shouldSkip($skip->format(self::DATE_TIME_FORMAT)));

        $iterator->skip($skip);

        $this->assertTrue($iterator->shouldSkip($skip));
        $this->assertTrue($iterator->shouldSkip($skip->format(self::DATE_TIME_FORMAT)));
    }

    /** @test */
    public function can_set_skip_multiple_times_will_add_on_and_it_will_skip_those_occurrences()
    {
        $endAfter = 5;
        $start = new DateTime('2014-06-12 12:03:30');
        $interval = new OneDayInterval();

        $iterator = $this->generateIterator($start, $interval, $endAfter);

        $iterator->skip(new DateTime('2014-06-15 12:03:30'));
        $iterator->skip(new DateTime('2014-06-17 12:03:30'));

        $expectedResults = [
            '2014-06-13 12:03:30',
            '2014-06-14 12:03:30',
//            '2014-06-15 12:03:30',
            '2014-06-16 12:03:30',
//            '2014-06-17 12:03:30',
            '2014-06-18 12:03:30',
            '2014-06-19 12:03:30',
        ];

        $count = 0;
        foreach($iterator as $occurrence) {
            $this->assertSame($expectedResults[$count], $occurrence->format(self::DATE_TIME_FORMAT));
            $count++;
        }

        $this->assertEquals($endAfter, $count);
    }

    /** @test */
    public function can_set_skip_array_with_datetime_and_it_will_skip_those_occurrences()
    {
        $endAfter = 6;
        $start = new DateTime('2014-06-12 12:03:30');
        $interval = new OneDayInterval();

        $iterator = $this->generateIterator($start, $interval, $endAfter);

        $iterator->skip([
            new DateTime('2014-06-15 12:03:30'),
            new DateTime('2014-06-17 12:03:30')
        ]);

        $expectedResults = [
            '2014-06-13 12:03:30',
            '2014-06-14 12:03:30',
//            '2014-06-15 12:03:30',
            '2014-06-16 12:03:30',
//            '2014-06-17 12:03:30',
            '2014-06-18 12:03:30',
            '2014-06-19 12:03:30',
            '2014-06-20 12:03:30',
        ];

        $count = 0;
        foreach($iterator as $occurrence) {
            $this->assertSame($expectedResults[$count], $occurrence->format(self::DATE_TIME_FORMAT));
            $count++;
        }

        $this->assertEquals($endAfter, $count);
    }

    /** @test */
    public function can_set_skip_with_datetime_and_it_will_skip_those_occurrences()
    {
        $endAfter = 6;
        $start = new DateTime('2014-06-12 12:03:30');
        $interval = new OneDayInterval();

        $iterator = $this->generateIterator($start, $interval, $endAfter);

        $iterator->skip(new DateTime('2014-06-14 12:03:30'));

        $expectedResults = [
            '2014-06-13 12:03:30',
//            '2014-06-14 12:03:30',
            '2014-06-15 12:03:30',
            '2014-06-16 12:03:30',
            '2014-06-17 12:03:30',
            '2014-06-18 12:03:30',
            '2014-06-19 12:03:30',
            '2014-06-23 12:03:30',
        ];

        $count = 0;
        foreach($iterator as $occurrence) {
            $this->assertSame($expectedResults[$count], $occurrence->format(self::DATE_TIME_FORMAT));
            $count++;
        }

        $this->assertEquals($endAfter, $count);
    }

    /** @test */
    public function can_set_skip_array_and_it_will_skip_those_occurrences()
    {
        $endAfter = 6;
        $start = new DateTime('2014-06-12 12:03:30');
        $interval = new OneDayInterval();

        $iterator = $this->generateIterator($start, $interval, $endAfter);

        $iterator->skip(['2014-06-15 12:03:30', '2014-06-17 12:03:30']);

        $expectedResults = [
            '2014-06-13 12:03:30',
            '2014-06-14 12:03:30',
//            '2014-06-15 12:03:30',
            '2014-06-16 12:03:30',
//            '2014-06-17 12:03:30',
            '2014-06-18 12:03:30',
            '2014-06-19 12:03:30',
            '2014-06-20 12:03:30',
        ];

        $count = 0;
        foreach($iterator as $occurrence) {
            $this->assertSame($expectedResults[$count], $occurrence->format(self::DATE_TIME_FORMAT));
            $count++;
        }

        $this->assertEquals($endAfter, $count);
    }

    /** @test */
    public function can_set_skip_and_it_will_skip_those_occurrences()
    {
        $endAfter = 6;
        $start = new DateTime('2014-06-12 12:03:30');
        $interval = new OneDayInterval();

        $iterator = $this->generateIterator($start, $interval, $endAfter);

        $iterator->skip('2014-06-14 12:03:30');

        $expectedResults = [
            '2014-06-13 12:03:30',
//            '2014-06-14 12:03:30',
            '2014-06-15 12:03:30',
            '2014-06-16 12:03:30',
            '2014-06-17 12:03:30',
            '2014-06-18 12:03:30',
            '2014-06-19 12:03:30',
        ];

        $count = 0;
        foreach($iterator as $occurrence) {
            $this->assertSame($expectedResults[$count], $occurrence->format(self::DATE_TIME_FORMAT));
            $count++;
        }

        $this->assertEquals($endAfter, $count);
    }

    /** @test */
    public function can_up_max_occurrences_after_initial_iteration_and_will_continue_to_get_more()
    {
        $datetime = new DateTime('2016-02-12');
        $interval = new TenDayInterval();
        $endAfter = '2016-07-14';

        $iterator = $this->generateIterator($datetime, $interval, $endAfter);
        $iterator->setEndAfter(9);

        $count = 0;
        foreach($iterator as $occurrence) {
            $count++;
        }

        $this->assertSame(9, $count);

        // Up the end after
        $iterator->setEndAfter($newEndAfter = 25);

        $count = 0;
        foreach($iterator as $occurrence) {
            $count++;
        }

        $this->assertSame($newEndAfter, $count);
    }

    /** @test */
    public function will_stop_running_after_max_occurrences_if_less_than_end_after()
    {
        $datetime = new DateTime('2016-02-12');
        $interval = new TenDayInterval();
        $endAfter = '2016-07-14';

        $iterator = $this->generateIterator($datetime, $interval, $endAfter);
        $iterator->setEndAfter(9);

        $count = 0;
        foreach($iterator as $occurrence) {
            $count++;
        }

        $this->assertSame(9, $count);
    }

    /** @test */
    public function will_stop_running_after_date_end_after()
    {
        $datetime = new DateTime('2016-02-12');
        $interval = new TenDayInterval();
        $endAfter = '2016-07-14';

        $iterator = $this->generateIterator($datetime, $interval, $endAfter);

        $count = 0;
        foreach($iterator as $occurrence) {
            $count++;
        }

        $this->assertSame(15, $count);
    }

    /** @test */
    public function will_stop_running_after_x_end_after()
    {
        $datetime = new DateTime('2016-02-12');
        $interval = new TenDayInterval();
        $endAfter = 19;

        $iterator = $this->generateIterator($datetime, $interval, $endAfter);

        $count = 0;
        foreach($iterator as $occurrence) {
            $count++;
        }

        $this->assertSame($endAfter, $count);
    }

    /** @test */
    public function iterator_will_cache_part_if_not_looped_all_through()
    {
        $datetime = new DateTime('2012-02-01');
        $interval = new TenDayInterval();
        $endAfter = 3;

        $expected = [
            '2012-02-11',
            '2012-02-21',
            '2012-03-02'
        ];

        $iterator = $this->generateIterator($datetime, $interval, $endAfter);

        $class = new ReflectionObject($iterator);
        $property = $class->getProperty('occurrences');
        $property->setAccessible(true);

        $this->assertEmpty($property->getValue($iterator));

        // Loop only first
        $firstOccurrence = null;
        foreach($iterator as $occurrence) {
            $firstOccurrence = $occurrence;
            break;
        }

        $this->assertCount(1, $property->getValue($iterator));
        $this->assertEquals($expected[0], $firstOccurrence->format('Y-m-d'));

        // Now iterate rest and check stores normally
        $count = 0;
        foreach($iterator as $occurrence) {
            $this->assertEquals($expected[$count], $occurrence->format('Y-m-d'));
            $count++;
        }

        // Check cached
        $this->assertCount(count($expected), $property->getValue($iterator));

        $mock = $this->getMockBuilder(TenDayInterval::class)
            ->setMethods([
                'findNextOccurrence'
            ])
            ->getMockForAbstractClass();

        // Will call once at end to verify out of range
        $mock->expects($this->once())
            ->method('findNextOccurrence')
            ->willReturn(new DateTime('2012-03-12'));

        $iterator->setInterval($mock);

        foreach($iterator as $occurrence) {
            // Iterating
        }
    }

    /** @test */
    public function after_iterating_will_have_cached_date_times()
    {
        $datetime = new DateTime('2012-02-01');
        $interval = new TenDayInterval();
        $endAfter = 3;

        $expected = [
            '2012-02-11',
            '2012-02-21',
            '2012-03-02'
        ];

        $iterator = $this->generateIterator($datetime, $interval, $endAfter);

        $class = new ReflectionObject($iterator);
        $property = $class->getProperty('occurrences');
        $property->setAccessible(true);

        $this->assertEmpty($property->getValue($iterator));

        // Now iterate and then check occurrences is stored
        count($iterator);

        $occurrences = $property->getValue($iterator);
        $this->assertNotEmpty($occurrences);

        // Check occurrences match expected
        $expectedCount = count($expected);
        $this->assertCount($expectedCount, $occurrences);
        for ($i = 0; $i < $expectedCount; $i++) {
            $this->assertEquals($expected[$i], $occurrences[$i]->format('Y-m-d'));
        }

        // If iterate again, check doesn't call get next occurrence
    }

    /** @test */
    public function valid_will_return_boolean_based_on_if_current_key_is_valid()
    {
        $datetime = new DateTime();
        $interval = new TenDayInterval();
        $endAfter = 18;
        $iterator = $this->generateIterator($datetime, $interval, $endAfter);

        // Will return false at start as occurrence hasn't yet been set
        $this->assertFalse($iterator->valid());

        $iterator->count();

        $this->assertFalse($iterator->valid());

        $iterator->rewind();
        $this->assertTrue($iterator->valid());
        // TODO: This shows a bug where after setting occurrences lower than the amount already got, iteration will fail
        // because the count exceeds the max
        $iterator->setMaxOccurrences(1);
        $this->assertFalse($iterator->valid());
    }

    /** @test */
    public function count_will_return_the_total_number_of_occurrences()
    {
        $datetime = new DateTime();
        $interval = new TenDayInterval();
        $endAfter = 18;
        $iterator = $this->generateIterator($datetime, $interval, $endAfter);

        $this->assertSame($endAfter, $iterator->count());

        // Can also use count(), re build iterator before
        $iterator = $this->generateIterator($datetime, $interval, $endAfter);
        $this->assertSame($endAfter, count($iterator));
    }

    /** @test */
    public function current_will_return_the_current_point_within_the_iterator()
    {
        $datetime = new DateTime();
        $interval = new TenDayInterval();
        $endAfter = 10;
        $iterator = $this->generateIterator($datetime, $interval, $endAfter);

        $this->assertNull($iterator->key());

        foreach($iterator as $occurrence) {
            $this->assertInstanceOf(DateTime::class, $occurrence);

            // Break after first iteration
            break;
        }

        // Key should be 1
        // Current should be date time plus 10 days
        $this->assertSame(0, $iterator->key());
        $this->assertInstanceOf(DateTime::class, $iterator->current());
        $cloned = clone $datetime;
        $this->assertSame($cloned->add(new DateInterval('P10D'))->getTimestamp(), $iterator->current()->getTimestamp());

        // Start iterator again to check currents and keys are correct
        $count = 0;
        foreach($iterator as $key => $occurrence) {
            $this->assertInstanceOf(DateTime::class, $iterator->current());

            $this->assertSame($key, $iterator->key());
            $this->assertSame($count, $iterator->key());

            $count++;
        }
    }

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
        $direction = IntervalInterface::FORWARDS;
        $iterator = call_user_func([self::ITERATOR_CLASS,'init'], $datetime, $interval, $endAfter, $direction);
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
        $cloned = clone $startDateTime;
        $endAfter = $cloned->add(new DateInterval('P10D'));
        $this->assertNotSame($endAfter, $iterator->getEndAfter());
        $iterator->setEndAfter($endAfter);

        $this->assertInstanceOf(DateTime::class, $iterator->getEndAfter());
        $this->assertSame($endAfter, $iterator->getEndAfter()); // referencing same object

        // Try with datetime string, check converted to datetime
        $format = 'Y-m-d H:i:s';
        $cloned = clone $endAfter;
        $endAfter = $cloned->add(new DateInterval('P10D'))->format($format);
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
        $cloned = clone $startDateTime;
        $pastDateTime = $cloned->sub(new DateInterval('P10D'));
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
        $cloned = clone $iterator->getStart();
        $datetime = $cloned->add(new DateInterval('P10D'));
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

        if (version_compare(PHP_VERSION, '7', '<=')) {
            $this->expectException(PHPUnit_Framework_Error::class);
        } else {
            $this->expectException(TypeError::class);
        }

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
        $direction = IntervalInterface::BACKWARDS;

        $classname = self::ITERATOR_CLASS;

        // Get mock, without the constructor being called
        $mock = $this->getMockBuilder($classname)
            ->disableOriginalConstructor()
            ->setMethods([
                'setStart',
                'setInterval',
                'setEndAfter',
                'setDirection'
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

        $mock->expects($this->once())
            ->method('setDirection')
            ->with($direction);

        // now call the constructor
        $reflectedClass = new ReflectionClass($classname);
        $constructor = $reflectedClass->getConstructor();
        $constructor->invoke($mock, $dateTime, $interval, $endAfter, $direction);
    }

    /**
     * HELPERS / PROVIDERS
     */

    /**
     * @return array
     */
    public function invalidDirections()
    {
        return [
            [new stdClass],
            [[]],
            [['test']],
            [[1,2,3]],
            [true],
            [false],
            ['test'],
            [12],
            [-1000],
            [-1]
        ];
    }

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
     * @param int $direction
     *
     * @return mixed
     */
    public function findNextOccurrence(DateTime $current, $direction = self::FORWARDS)
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
     * @param int $direction
     *
     * @return mixed
     */
    public function findNextOccurrence(DateTime $current, $direction = self::FORWARDS)
    {
        return $current;
    }
}

/**
 * Class TenDayInterval
 */
class TenDayInterval implements IntervalInterface
{
    /**
     * Method that finds the next occurrence of the interval from current
     *
     * @param DateTime $current
     * @param int $direction
     *
     * @return mixed
     */
    public function findNextOccurrence(DateTime $current, $direction = self::FORWARDS)
    {
        $cloned = clone $current;
        return $cloned->add(new DateInterval('P10D'));
    }
}

/**
 * Class OneDayInterval
 */
class OneDayInterval implements IntervalInterface
{
    /**
     * Method that finds the next occurrence of the interval from current
     *
     * @param DateTime $current
     * @param int $direction
     *
     * @return mixed
     */
    public function findNextOccurrence(DateTime $current, $direction = self::FORWARDS)
    {
        $interval = new DateInterval('P1D');
        $cloned = clone $current;

        if ($direction === self::BACKWARDS) {
            return $cloned->sub($interval);
        } else {
            return $cloned->add($interval);
        }
    }
}