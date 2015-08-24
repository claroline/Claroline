<?php

namespace FormaLibre\ReservationBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use JMS\DiExtraBundle\Annotation as DI;

/**
 * @DI\Validator("daterange_validator")
 */
class DateRangeValidator extends ConstraintValidator
{
    public function validate($object, Constraint $constraint)
    {
        if ($object->getStart() === null) {
            $this->context->addViolation('valid_start_date_required');
        }

        if ($object->getStart() !== null && $object->getEnd() !== null && $object->getStart() > $object->getEnd()) {
                $this->context->addViolation($constraint->message);
        }

        if ($object->getEnd() === null) {
            $this->context->addViolation('valid_end_date_required');
        }
    }
}
