<?php

use DateIntervalIterator\Occurrences;
use DateIntervalIterator\Exceptions\InvalidArgumentException;

/**
 * Class OccurrencesTest
 */
class OccurrencesTest extends PHPUnit_Framework_TestCase
{
    // Tests:
    //  - Can't set a non datetime as an occurrence (either via offsetSet or push) - done
    //  - Can set a datetime as an occurrence (either via offsetSet [], or push) - done
    //  - Can access / set Occurrences as an array using array notation - done
    //  - Push will add a new item to the end of the occurrences array and return self - done
    //  - Pop will remove the last element from the occurrences array and return it (will be a datetime instance) - done
    //  - Can output all as an array using getOccurrences and toArray - done
    //  - Can count the number of elements using ->count() and count($occurrences) - done
    //  - Can pass initial array during object construction - done

    /** @test */
    public function can_count_all_elements_in_array_using_count_function_and_method()
    {
        $startingOccurrences = $this->validDateTimes();
        $occurrences = new Occurrences($startingOccurrences);

        $countFunctionResult = count($occurrences);
        $this->assertSame(count($startingOccurrences), $countFunctionResult);

        $countMethodResult = $occurrences->count();
        $this->assertSame(count($startingOccurrences), $countMethodResult);
    }

    /** @test */
    public function can_get_all_occurrences_as_an_array_with_toArray_and_get_occurrences()
    {
        $startingOccurrences = $this->validDateTimes();
        $occurrences = new Occurrences($startingOccurrences);

        $occurrencesArray = $occurrences->getOccurrences();
        $this->assertTrue(is_array($occurrencesArray));
        $this->assertSame($startingOccurrences, $occurrencesArray);

        // Now try toArray
        $occurrencesArray = $occurrences->toArray();
        $this->assertTrue(is_array($occurrencesArray));
        $this->assertSame($startingOccurrences, $occurrencesArray);
    }

    /** @test */
    public function can_pass_initial_array_in_constructor()
    {
        $startingOccurrences = $this->validDateTimes();
        $occurrences = new Occurrences($startingOccurrences);
        $this->assertSame(count($startingOccurrences), $occurrences->count());
        $this->assertSame($startingOccurrences, $occurrences->getOccurrences());
    }

    /** @test */
    public function pop_will_remove_last_element_in_occurrence_array()
    {
        $occurrences = new Occurrences();
        $dateTimeOne = new DateTime();
        $dateTimeTwo = (new DateTime())->modify('+1 month');

        $occurrences->push($dateTimeOne);
        $occurrences->push($dateTimeTwo);

        // Ensure there are 2 and the last element matches set
        $this->assertSame(2, $occurrences->count());
        $this->assertEquals($dateTimeTwo->getTimestamp(), $occurrences->toArray()[1]->getTimestamp());

        // Now pop
        $return = $occurrences->pop();
        $this->assertSame(1, $occurrences->count());
        $this->assertFalse(isset($occurrences[1]));
        $this->assertInstanceOf(DateTime::class, $return);
        $this->assertSame($dateTimeTwo->getTimestamp(), $return->getTimestamp());
    }

    /** @test */
    public function push_will_add_to_end_of_occurrence_array_and_return_object()
    {
        $occurrences = new Occurrences();
        $dateTimeOne = new DateTime();
        $dateTimeTwo = (new DateTime())->modify('+1 month');

        $this->assertInstanceOf(Occurrences::class, $occurrences->push($dateTimeOne));
        $this->assertSame(1, $occurrences->count());
        $this->assertEquals($dateTimeOne->getTimestamp(), $occurrences->toArray()[0]->getTimestamp());

        $this->assertInstanceOf(Occurrences::class, $occurrences->push($dateTimeTwo));
        $this->assertSame(2, $occurrences->count());
        $this->assertEquals($dateTimeTwo->getTimestamp(), $occurrences->toArray()[1]->getTimestamp());
    }

    /** @test */
    public function can_access_object_as_using_array_syntax()
    {
        $occurrences = new Occurrences();
        $dateTimeOne = new DateTime();
        $dateTimeTwo = new DateTime();
        $dateTimeThree = (new DateTime())->modify('+1 month');

        $occurrences->push($dateTimeOne);
        $this->assertSame(1, $occurrences->count());

        $occurrences[] = $dateTimeTwo;
        $this->assertSame(2, $occurrences->count());

        // Now attempt to set directly to an offset (ensure not same so next test isn't false positive)
        $this->assertNotEquals($dateTimeTwo->getTimestamp(), $dateTimeThree->getTimestamp());
        $occurrences[1] = $dateTimeThree;
        $this->assertSame(2, $occurrences->count());

        // Attempt to get from array
        $dateTimeThreeToTest = $occurrences[1];
        $this->assertInstanceOf(DateTime::class, $dateTimeThreeToTest);
        $this->assertSame($dateTimeThree->getTimestamp(), $dateTimeThreeToTest->getTimestamp());

        // Attempt to unset
        unset($occurrences[1]);
        $this->assertSame(1, $occurrences->count());
    }

    /**
     * @dataProvider validDateTimes
     * @test
     *
     * @param DateTime $validDateTime
     */
    public function can_set_valid_datetime_as_occurrence($validDateTime)
    {
        $occurrences = new Occurrences();

        $this->assertEmpty($occurrences->toArray());

        $occurrences->push($validDateTime);
        $this->assertEquals($validDateTime->getTimestamp(), $occurrences->toArray()[0]->getTimestamp());

        $occurrences = new Occurrences();
        $this->assertEmpty($occurrences->toArray());

        $occurrences[] = $validDateTime;
        $this->assertEquals($validDateTime->getTimestamp(), $occurrences->toArray()[0]->getTimestamp());
    }

    /**
     * @dataProvider invalidDateTimes
     * @test
     *
     * @param $invalidDateTime
     */
    public function cant_push_non_datetime_as_occurrence($invalidDateTime)
    {
        $occurrences = new Occurrences();

        $this->setExpectedException(
            InvalidArgumentException::class,
            'You must pass a valid DateTime instance through as an occurrence'
        );

        $occurrences->push($invalidDateTime);
    }

    /**
     * @dataProvider invalidDateTimes
     * @test
     *
     * @param $invalidDateTime
     */
    public function cant_set_non_datetime_as_occurrence($invalidDateTime)
    {
        $occurrences = new Occurrences();

        $this->setExpectedException(
            InvalidArgumentException::class,
            'You must pass a valid DateTime instance through as an occurrence'
        );

        $occurrences[] = $invalidDateTime;
    }

    /**
     *
     * PROVIDERS AND HELPERS
     *
     */

    /**
     * @return array
     */
    public function invalidDateTimes()
    {
        return [
            [1],
            [[]],
            [new stdClass()],
            ['test'],
            [''],
            [[1,2,3]]
        ];
    }

    /**
     * @return array
     */
    public function validDateTimes()
    {
        return [
            [new DateTime()],
            [(new DateTime())->modify('+1 month')]
        ];
    }
}