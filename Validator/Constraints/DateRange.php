<?php

namespace FormaLibre\ReservationBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 */
class DateRange extends Constraint
{
    public $message = 'invalid_date_range';

    public function validatedBy()
    {
        return 'daterange_validator';
    }

    public function getTargets()
    {
        return self::CLASS_CONSTRAINT;
    }
}