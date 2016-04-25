<?php

namespace FormaLibre\ReservationBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 */
class ReservationModify extends Constraint
{
    public $message = 'invalid_reservation';

    public function validatedBy()
    {
        return 'reservation_modify_validator';
    }

    public function getTargets()
    {
        return self::CLASS_CONSTRAINT;
    }
}
