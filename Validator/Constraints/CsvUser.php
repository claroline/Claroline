<?php

namespace Claroline\CoreBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 */
class CsvUser extends Constraint
{
    public $message = 'Each row requires at least 5 parameters.';

    public function validatedBy()
    {
        return 'csv_user_validator';
    }
}
