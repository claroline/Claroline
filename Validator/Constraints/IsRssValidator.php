<?php

namespace Claroline\RssReaderBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class IsRssValidator extends ConstraintValidator
{
    public function validate($value, Constraint $constraint)
    {
        $content = @file_get_contents($value);

        if (!$content) {
            $this->context->addViolation($constraint->message, array('{{ username }}' => $value));
        }
    }
}

