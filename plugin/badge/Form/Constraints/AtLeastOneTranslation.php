<?php

namespace Icap\BadgeBundle\Form\Constraints;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 */
class AtLeastOneTranslation extends Constraint
{
    public $message = 'badge_need_at_least_one_translation';

    public function getTargets()
    {
        return self::CLASS_CONSTRAINT;
    }
}
