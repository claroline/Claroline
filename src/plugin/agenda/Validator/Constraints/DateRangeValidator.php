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

class DateRangeValidator extends ConstraintValidator
{
    public function validate($object, Constraint $constraint)
    {
        // If the checkbox isAllDay is checked and isTask is unchecked
        if ($object->isAllDay() && !$object->isTask()) {
            if (null === $object->getStart()) {
                $this->context->addViolation('valid_start_date_required');
            }
        }
        // If isAllDay and isTask are unchecked
        elseif (!$object->isAllDay() && !$object->isTask()) {
            if (null === $object->getStart()) {
                $this->context->addViolation('valid_start_date_required');
            }

            if (null !== $object->getStart() && null !== $object->getEnd()) {
                if ($object->getStartInTimestamp() > $object->getEndInTimestamp()) {
                    $this->context->addViolation('invalid_date_range');
                }
            }

            if ($object->getEndInTimestamp() - $object->getStartInTimestamp() < 60 * 15) {
                $this->context->addViolation('date_range_too_small');
            }
        }

        if (null === $object->getEnd()) {
            $this->context->addViolation('valid_end_date_required');
        }
    }
}
