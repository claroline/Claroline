<?php

namespace UJM\ExoBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class isNotEmptyValidator extends ConstraintValidator
{
    public function validate($value, Constraint $constraint)
    {
        if (empty($value)) {
            $this->context->addViolation($constraint->message);
        }
    }
}
