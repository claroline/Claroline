<?php

namespace UJM\ExoBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 */
class isValidQCMMark extends Constraint
{
    public $message = 'is_valid_qcm_mark';

    public function validatedBy()
    {
        return 'ujm.exercise_isvalidqcmmark';
    }
}