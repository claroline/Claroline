<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Validator\Constraints;

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
        if (!$object->getAllDay()) {

            //check if the startHours and endHours are valid
            if ($object->startHours === null) {
                $this->context->addViolation('valid_start_hour_required');
            }

            if ($object->endHours === null) {
                $this->context->addViolation('valid_end_hour_required');
            }

            if ($object->getStart() === null) {
                $this->context->addViolation('valid_start_date_required');
            }

            if ($object->getEnd() === null) {
                $this->context->addViolation('valid_end_date_required');
            }

            if ($object->endHours === null || $object->startHours === null) {
                return;
            }

            if ($object->getStart()->getTimeStamp() + $object->startHours >
                $object->getEnd()->getTimeStamp()  + $object->endHours
            ) {
                $this->context->addViolation($constraint->message);
            }
        }
    }
} 
