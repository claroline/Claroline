<?php
/**
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * Author: Panagiotis TSAVDARIS
 *
 * Date: 1/10/17
 */

namespace Claroline\CoreBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 */
class UserAdministrativeCode extends Constraint
{
    public $error = 'user_admin_code_already_used';

    public function validatedBy()
    {
        return 'user_administrative_code_validator';
    }

    public function getTargets()
    {
        return self::CLASS_CONSTRAINT;
    }
}
