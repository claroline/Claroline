<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\AgendaBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use JMS\DiExtraBundle\Annotation as DI;

/**
 * @DI\Validator("claroline_daterange_validator")
 */
class DateRangeValidator extends ConstraintValidator
{
    public function validate($object, Constraint $constraint)
    {
        // If the checkbox isAllDay is checked and isTask is unchecked
        if ($object->isAllDay() && !$object->isTask()) {
            if ($object->getStart() === null) {
                $this->context->addViolation('valid_start_date_required');
            }
        }
        // If isAllDay and isTask are unchecked
        elseif (!$object->isAllDay() && !$object->isTask()) {
            if ($object->getStart() === null) {
                $this->context->addViolation('valid_start_date_required');
            }

            if ($object->getStart() !== null && $object->getEnd() !== null) {
                if ($object->getStartInTimestamp() > $object->getEndInTimestamp()) {
                    $this->context->addViolation('invalid_date_range');
                }
            }

            if ($object->getEndInTimestamp() - $object->getStartInTimestamp() < 60 * 15) {
                $this->context->addViolation('date_range_too_small');
            }
        }

        if ($object->getEnd() === null) {
            $this->context->addViolation('valid_end_date_required');
        }
    }
}
