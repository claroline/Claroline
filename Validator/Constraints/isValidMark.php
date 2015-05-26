<?php

namespace UJM\ExoBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 */
class isValidMark extends Constraint
{
    public $message = 'is_valid_qcm_mark';
}