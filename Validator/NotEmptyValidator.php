<?php

namespace HeVinci\CompetencyBundle\Validator;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class NotEmptyValidator extends ConstraintValidator
{
    public function validate($value, Constraint $constraint)
    {
        if (!is_array($value) && !$value instanceof \Countable) {
            throw new \InvalidArgumentException(
                'array or Countable instance expected'
            );
        }

        if (count($value) === 0) {
            $this->context->addViolation($constraint->message);
        }
    }
}
