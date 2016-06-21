<?php

namespace FormaLibre\ReservationBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 */
class DateRange extends Constraint
{
    public function validatedBy()
    {
        return 'formalibre_daterange_validator';
    }

    public function getTargets()
    {
        return self::CLASS_CONSTRAINT;
    }
}
