<?php

namespace UJM\ExoBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 */
class isValidQCMMark extends Constraint
{
    public $message = 'La note "%string%" n\'est pas une note valide';
    
    public function validatedBy()
    {
        return 'ujm.exercise_isvalidqcmmark';
    }
}