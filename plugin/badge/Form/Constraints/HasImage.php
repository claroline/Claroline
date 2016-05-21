<?php

namespace Icap\BadgeBundle\Form\Constraints;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 */
class HasImage extends Constraint
{
    public $message = 'badge_need_image';

    public function getTargets()
    {
        return self::CLASS_CONSTRAINT;
    }
}
