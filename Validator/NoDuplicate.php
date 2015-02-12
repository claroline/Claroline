<?php

namespace HeVinci\CompetencyBundle\Validator;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 */
class NoDuplicate extends Constraint
{
    public $message = 'Each value must be unique.';
}
