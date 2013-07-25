<?php

namespace UJM\ExoBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 */
class isValidQCMGlobalMark extends Constraint
{
    public $message = 'La note "%string%" n\'est pas une note valide';
    
    public function validatedBy()
    {
        return 'ujm.exercise_isvalidqcmglobalmark';
    }
}