<?php

namespace Claroline\CoreBundle\Form\Badge\Constraints;

use Claroline\CoreBundle\Entity\Badge\Badge;
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
        if ($badge->getAutomaticAward() && 0 >= count($badge->getBadgeRules())) {
            $this->context->addViolation($constraint->message, array(), null);
        }
    }
}