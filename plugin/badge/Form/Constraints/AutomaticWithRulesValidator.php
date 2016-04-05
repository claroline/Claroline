<?php

namespace Icap\BadgeBundle\Form\Constraints;

use Icap\BadgeBundle\Entity\Badge;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class AutomaticWithRulesValidator extends ConstraintValidator
{
    /**
     * @param Badge      $badge
     * @param Constraint $constraint
     */
    public function validate($badge, Constraint $constraint)
    {
        if ($badge->getAutomaticAward() && 0 >= count($badge->getRules())) {
            $this->context->addViolation($constraint->message, array(), null);
        }
    }
}
