<?php

namespace Icap\BadgeBundle\Form\Constraints;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 */
class CheckExpiringPeriod extends Constraint
{
    public $message = 'badge_expiring_need_period_and_duration';

    public function getTargets()
    {
        return self::CLASS_CONSTRAINT;
    }
}
