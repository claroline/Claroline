<?php

namespace FormaLibre\ReservationBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 */
class Reservation extends Constraint
{
    public $message = 'invalid_reservation';

    public function validatedBy()
    {
        return 'reservation_validator';
    }

    public function getTargets()
    {
        return self::CLASS_CONSTRAINT;
    }
}
