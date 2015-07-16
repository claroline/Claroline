<?php

namespace UJM\ExoBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class isNotEmptyValidator extends ConstraintValidator
{
    public function validate($value, Constraint $constraint)
    {
        $valueT=trim($value);
        if (isset($valueT)) {
            if(preg_match('/^$|\s+/',$valueT)){
              
                $this->context->addViolation($constraint->message);
            }
        }
    }
}
