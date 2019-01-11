<?php
namespace JRBarnard\Recurrence\Intervals;

use JRBarnard\Recurrence\Exceptions\InvalidArgumentException;

trait SetsDays
{
    /**
     * @var array
     */
    protected $days = [];

    /**
     * Override in class if needed
     * @var bool
     */
    //protected $allowEmptyDays = false;

    /**
     * @return bool
     */
    public function allowEmptyDays()
    {
        return property_exists($this, 'allowEmptyDays') ? $this->allowEmptyDays : false;
    }

    /**
     * @param array $days
     *
     * @return $this
     */
    public function setDays($days = self::DAYS_OF_WEEK)
    {
        $days = (array) $days;

        $exceptionMessage = 'You must pass valid days of the week to the interval';

        if (!$this->allowEmptyDays() && empty($days)) {
            throw new InvalidArgumentException($exceptionMessage);
        }

        // Loop over the days passed in and verify all are valid
        $daysToSet = [];
        foreach ($days as $day) {
            if (!$this->isValidDay($day)) {
                throw new InvalidArgumentException($exceptionMessage);
            }

            // Only add in if not already added in
            if (!in_array($day, $daysToSet)) {
                $daysToSet[] = $day;
            }
        }

        // Sort before they go in
        sort($daysToSet);
        $this->days = $daysToSet;

        return $this;
    }

    /**
     * @param $day
     *
     * @return bool
     */
    protected function isValidDay($day)
    {
        if (in_array($day, self::DAYS_OF_WEEK, true)) {
            return true;
        }

        return false;
    }

    /**
     * @return array
     */
    public function getDays()
    {
        return $this->days;
    }
}
