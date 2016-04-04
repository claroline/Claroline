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
class AdminWorkspaceTagUniqueName extends Constraint
{
    public $message = 'The name {{ name }} is already in use.';

    public function validatedBy()
    {
        return 'admin_workspace_tag_unique_name_validator';
    }
}
