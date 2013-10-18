<?php

namespace Claroline\CoreBundle\Form\Badge\Constraints;

use Claroline\CoreBundle\Entity\Badge\Badge;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class AtLeastOneTranslationValidator extends ConstraintValidator
{
    /**
     * @param Badge      $badge
     * @param Constraint $constraint
     */
    public function validate($badge, Constraint $constraint)
    {
        $frTranslation = $badge->getFrTranslation();
        $enTranslation = $badge->getEnTranslation();

        $frName        = $frTranslation->getName();
        $frDescription = $frTranslation->getDescription();
        $frCriteria    = $frTranslation->getCriteria();

        $enName        = $enTranslation->getName();
        $enDescription = $enTranslation->getDescription();
        $enCriteria    = $enTranslation->getCriteria();

        //Have to put all method call in variable because of empty doesn't support result of method as parameter (prior to PHP 5.5)
        $hasFrTranslation = (!empty($frName) && !empty($frDescription) && !empty($frCriteria)) ? true : false;
        $hasEnTranslation = (!empty($enName) && !empty($enDescription) && !empty($enCriteria)) ? true : false;

        if (!$hasFrTranslation && !$hasEnTranslation) {
            $this->context->addViolation($constraint->message);
        }
    }
}