<?php

namespace Icap\BadgeBundle\Form\Constraints;

use Icap\BadgeBundle\Entity\Badge;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class HasImageValidator extends ConstraintValidator
{
    /**
     * @param Badge      $badge
     * @param Constraint $constraint
     */
    public function validate($badge, Constraint $constraint)
    {
        if (null === $badge->getFile() && null === $badge->getImagePath()) {
            $this->context->addViolationAt('file', $constraint->message);
        }
    }
}
