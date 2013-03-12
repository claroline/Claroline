<?php

namespace Claroline\RssReaderBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class IsRssValidator extends ConstraintValidator
{
    public function validate($value, Constraint $constraint)
    {
        try {
            file_get_contents($value);

            return true;
        } catch (\Exception $e) {
            $this->context->addViolation($constraint->message, array('{{ username }}' => $value));

            return false;
        }
    }
}

