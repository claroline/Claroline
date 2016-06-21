<?php

namespace FormaLibre\ReservationBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 */
class Duration extends Constraint
{
    public $message = 'invalid_time_duration';

    public function validatedBy()
    {
        return 'formalibre_duration_validator';
    }

    public function getTargets()
    {
        return self::CLASS_CONSTRAINT;
    }
}
