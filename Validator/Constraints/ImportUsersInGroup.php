<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 */
class ImportUsersInGroup extends Constraint
{
    public $message = 'Each row only require the username';

    public function validatedBy()
    {
        return 'import_user_in_group_validator';
    }
}
