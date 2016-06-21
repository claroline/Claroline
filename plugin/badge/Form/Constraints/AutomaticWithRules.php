<?php

namespace Icap\BadgeBundle\Form\Constraints;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 */
class AutomaticWithRules extends Constraint
{
    public $message = 'badge_automatic_awarding_need_rules';

    public function getTargets()
    {
        return self::CLASS_CONSTRAINT;
    }
}
