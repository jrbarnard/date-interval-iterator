# Recurrence #

[![Build Status](https://travis-ci.org/jrbarnard/recurrence.svg?branch=master)](
https://travis-ci.org/jrbarnard/recurrence)
[![StyleCI](https://styleci.io/repos/77830859/shield?branch=master)](https://styleci.io/repos/77830859)

This library allows you to create recurring DateTimes based on intervals, e.g every last wednesday of the month for the
next 2 years.

It comes with some core Intervals, but it's built to allow you to create and use your own for your own project
requirements.

It acts much like the DatePeriod PHP iterator in that it accepts a start date, end date and an interval object, however
it gives us some more functionality also.

## Contents ##

1. [Requirements](#req)
2. [Installation](#install)
3. [Usage](#usage)
4. [Contributing](#contrib)
5. [License](#license)

## <a name=req>Requirements</a> ##

The only requirement currently is a minimum PHP version of 5.6.

CURRENTLY HACK / HHVM IS NOT SUPPORTED

## <a name=install>Installation</a> ##

Composer is the recommended installation method:
```
composer require jrbarnard/date-interval-iterator
```

However you can also download this repo, unzip it and include it in your project.

## <a name=usage>Usage</a> ##

The package comprises of three class types, these can be used separately or all together.

### Occurrences ###

The occurrences class is a container to store DateTimes. It's basically just a wrapper around an array, but allows us 
a bit more flexibility in the future if we want to deal with a collection of DateTimes / the stored occurrences from
the iterator.

```php
$occurrences = new Occurrences();

// Add an occurrence in
$occurrences->push(new DateTime);

// $popped will be the last added occurrence
$popped = $occurrences->pop();

$occurrences->push(new DateTime);

// Can get an occurrence by index
$occurrence = $occurrences->getOccurrence(0);
```

Occurrences also implements the ArrayAccess interface meaning we can use the occurrences object just like an array:
```php
$occurrences = new Occurrences();

// Same as push
$occurrences[] = new DateTime();

// $occurrence will be the 0th index DateTime object
$occurrence = $occurrences[0];
```

Currently it doesn't have any internal validation, fancier methods for filtering etc. These are things we may add in 
the future.

### Intervals ###

Intervals are the classes that define how we get the next occurrence within the iterator. They mirror the functionality
of PHP's core DateIntervals where their purpose is to take a datetime and return back a datetime after applying their
interval to it.

E.g Datetime of 2012-01-01, interval of 1 day, we would expect to get back a datetime of 2012-01-02.

Intervals can be as simple or as complex as you want, we provide a few, however you can make and use your own, they
just must implement the IntervalInterface.

E.g:
```php

use JRBarnard\DateIntervalIterator\Intervals\IntervalInterface;

class ExampleInterface implements IntervalInterface
{
    /**
     * Method that finds the next occurrence of the interval from current
     *
     * @param DateTime $current
     * @param $direction
     *
     * @return DateTime
     */
    public function findNextOccurrence(DateTime $current, $direction = self::FORWARDS)
    {
        $interval = new \DateInterval('PT12M');
        $cloned = clone $current;
        
        if ($direction === self::BACKWARDS) {
            return $cloned->sub($interval);
        } else {
            return $cloned->add($interval);   
        }
    }
}
```

The above example would apply the interval of 12 minutes to the date time, you can see this very basic interval is at 
it's core utilising a PHP DateInterval. This works well for simple Intervals, however may not be suitable for more
complex ones.

Intervals also accept a direction, forwards or backwards, you can use the constants defined within the 
IntervalInterface.

The intervals that come with the package as standard are:

* DailyInterval - Accepts an integer number of days and will add / sub that number of days from the passed in DateTime
```php
new DailyInterval(12);
(new DailyInterval())->setNumberOfDays(12);
```
* HourlyInterval - Accepts a numeric (can be float) number of hours and will add / sub that number of minutes from
the passed in DateTime - E.g if you pass 3.5, that will equate to 210 minutes. If you pass 1.472826 that will equate to
88 minutes.
```php
new HourlyInterval(1.5);
(new HourlyInterval())->setNumberOfHours(1.5);
```
* WeeklyInterval - Accepts days of the week and the weekly occurrence, so you can do intervals such as: every Tuesday
and Wednesday of every 3rd week.
```php
// Basic usage via constructor
new WeeklyInterval([WeeklyInterval::WEDNESDAY, WeeklyInterval::TUESDAY], 3);

// Magic setters
(new WeeklyInterval())->everyTuesday()->andEveryWednesday()->ofEvery3rdWeek();

// Standard setters
(new WeeklyInterval())->every([WeeklyInterval::TUESDAY, WeeklyInterval::WEDNESDAY])->ofEveryWeek(3);
```
* MonthlyInterval - Accepts days of the week and the monthly occurrence, so you can do intervals such as: the last 
Wednesday and Thursday of every other month. - COMING SOON
```php
// Basic usage via constructor
new MonthlyInterval(MonthlyInterval::LAST, [MonthlyInterval::WEDNESDAY, MonthlyInterval::THURSDAY], 2);

// Magic setters
(new MonthlyInterval())->everyLast()->wednesday()->andThursday()->ofEvery2ndMonth();

// Standard setters
(new MonthlyInterval())->every(MonthlyInterval::LAST, [MonthlyInterval::WEDNESDAY, MonthlyInterval::THURSDAY])->ofEveryMonth(2);
```

### Date Interval Iterator ###

The Iterator is the class you can use to apply your intervals over a set DateTime / Occurrence range.

For example it lets you do things like:
- Get an occurrence every hour and a half until next week:
```php
$start = new DateTime(); // Now
$end = (new DateTime())->add(new DateInterval('P1W'));

$interval = new HourlyInterval(1.5);

$iterator = new DateIntervalIterator($start, $interval, $end);

foreach($iterator as $occurrence) {
    // $occurrence will be a DateTime instance of every hour and half till next week
}
```
- Get the next 50 occurrences of every 2nd Wednesday and Thursdays:
```php
$start = new DateTime(); // Now
$end = 50;

$interval = new WeeklyInterval([WeeklyInterval::WEDNESDAY, WeeklyInterval::THURSDAY], 2);

$iterator = new DateIntervalIterator($start, $interval, $end);

foreach($iterator as $occurrence) {
    // $occurrence will be a DateTime instance of every 2nd Wednesday and Thursday up to 50 occurrences.
}
```

This allows us to calculate dates for things like recurring events easily.

As mentioned above, you can create your own intervals, so you should be able to create an interval /  iterator to fit 
your own needs.

Iterators also have a few extra features:
* They are countable, if all you want is how many times an occurrence will occur you can do:
```php
$iterator->count();
// Or:
count($iterator);
```
* You can specify to skip certain DateTimes: E.g if you want an occurrence every hour and half, but not one of the 
occurrences for whatever reason, you can:
```php
$iterator->skip(new DateTime('2012-02-02 12:30:00'));

// You can also check if the iterator should skip a particular datetime:
$iterator->shouldSkip('2012-02-02 12:30:00');
// Will return true
```
* You can set when the iterator should end to a DateTime or a number of occurrences, for example you may want to get an 
occurrence every hour before 5:30pm on a certain day, you can and it will stop iterating when the next occurrence it 
would get goes beyond this limit. Or if you prefer to set a number of occurrence limit, e.g every hour for the next 10 
hours, you can by just passing an int:
```php
$iterator->setEndAfter(10);
$iterator->setEndAfter('2016-12-12 17:30:00');

// You can also use a getter:
$iterator->getEndAfter();
```
* You can set a hard upper limit on number of occurrences to prevent the iterator accidentally iterating too much:
```php
// It defaults to 100 when you instantiate the iterator
$iterator->setMaxOccurrences(20);

// If you now try and set an end after over the max it will throw an exception
// If you set an end after to a date, it wil stop iterating either when it reaches that date or the max occurrences,
// whichever occurs first.
```
* You can set a direction for the iterator, so you can get the say every last Wednesday of every month from now, back 2
years:
```php
$iterator->setDirection(IntervalInterface::BACKWARDS);
```
* You can get the occurrences object from the iterator, it will internally run the iterator to ensure it's populated
before returning:
```php
$iterator->getOccurrences();
// Will return an instance of Occurrences
```
* You can also run iterators in reverse by setting a direction:
```php
$iterator->setDirection(IntervalInterface::BACKWARDS);
```

## <a name=contrib>Contributing</a> ##

[Please look at the contributing file](CONTRIBUTING.md)

## <a name=license>License</a> ##

[MIT License](LICENSE)