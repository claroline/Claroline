<?php

namespace UJM\ExoBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;


/**
 * @Annotation
 */
class isNotEmpty extends Constraint
{
    public $message = 'Vous devez remplir le champs';
}