<?php

namespace HeVinci\CompetencyBundle\Validator;

use Symfony\Component\Validator\Constraint;

class ExistingAbility extends Constraint
{
    public $message = 'This ability does not exist.';

    public function validatedBy()
    {
        return 'existing_ability_validator';
    }
}
