<?php

namespace Icap\BadgeBundle\Form\Constraints;

use Icap\BadgeBundle\Entity\Badge;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class CheckExpiringPeriodValidator extends ConstraintValidator
{
    /**
     * @param Badge      $badge
     * @param Constraint $constraint
     */
    public function validate($badge, Constraint $constraint)
    {
        if ($badge->isExpiring()) {
            if (null === $badge->getExpireDuration()) {
                $this->context->addViolationAt('expire_duration', $constraint->message);
            }
        }
    }
}
