<?php

namespace HeVinci\CompetencyBundle\Validator;

use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class NotEmptyValidator extends ConstraintValidator
{
    public function validate($value, Constraint $constraint)
    {
        if (!$value instanceof ArrayCollection) {
            throw new \InvalidArgumentException('ArrayCollection expected');
        }

        if ($value->count() === 0) {
            $this->context->addViolation($constraint->message);
        }
    }
}
