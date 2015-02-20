<?php

namespace HeVinci\CompetencyBundle\Validator;

use Symfony\Component\Validator\Constraint;

/**
 * Note: this constraint requires a runtime parameter (the parent
 *       competency) and therefore cannot be used as an annotation.
 */
class UniqueCompetency extends Constraint
{
    public $message = 'This value is already used.';
    public $parentCompetency = null;

    public function validatedBy()
    {
        return 'competency_name_validator';
    }

    public function getTargets()
    {
        return self::CLASS_CONSTRAINT;
    }
}
