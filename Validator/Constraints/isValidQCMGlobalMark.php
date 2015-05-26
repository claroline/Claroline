<?php

namespace UJM\ExoBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 */
class isValidQCMGlobalMark extends Constraint
{
    public $message = 'is_valid_qcm_mark';

    public function validatedBy()
    {
        return 'ujm.exercise_isvalidqcmglobalmark';
    }
}