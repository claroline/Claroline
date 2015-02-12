<?php

namespace HeVinci\CompetencyBundle\Validator;

use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class NoDuplicateValidator extends ConstraintValidator
{
    public function validate($value, Constraint $constraint)
    {
        if (!$value instanceof ArrayCollection) {
            throw new \InvalidArgumentException('ArrayCollection expected');
        }

        $items = [];

        foreach ($value as $object) {
            if (in_array($item = $object->getName(), $items)) {
                $this->context->addViolation($constraint->message);
                break;
            }

            $items[] = $item;
        }
    }
}
