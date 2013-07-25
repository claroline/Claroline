<?php

namespace UJM\ExoBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 */
class isValidMark extends Constraint
{
    public $message = 'La note "%string%" n\'est pas une note valide';
}