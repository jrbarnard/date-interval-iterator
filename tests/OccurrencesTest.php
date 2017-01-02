<?php

class OccurrencesTest extends PHPUnit_Framework_TestCase
{
    // Tests:
    //  - Can't set a non datetime as an occurrence (either via offsetSet or push)
    //  - Can access / set Occurrences as an array using array notation
    //  - Push will add a new item to the end of the occurrences array and return self
    //  - Pop will remove the last element from the occurrences array and return it (will be a datetime instance)
    //  - Can output all as an array using getOccurrences
    //  - Can count the number of elements using ->count() and count($occurrences)

}