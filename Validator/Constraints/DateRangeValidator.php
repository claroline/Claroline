<?php

namespace FormaLibre\ReservationBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use JMS\DiExtraBundle\Annotation as DI;

/**
 * @DI\Validator("formalibre_daterange_validator")
 */
class DateRangeValidator extends ConstraintValidator
{
    public function validate($object, Constraint $constraint)
    {
        if (!$object->getStartInTimestamp()) {
            $this->context->addViolation('valid_start_date_required');
        }

        if ($object->getStartInTimestamp() && $object->getEndInTimestamp() !== null && $object->getStartInTimestamp() >= $object->getEndInTimestamp()) {
            $this->context->addViolation('valid_date_range_required');
        }

        if (!$object->getEndInTimestamp()) {
            $this->context->addViolation('valid_end_date_required');
        }
    }
}
