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
class WorkspaceUniqueCode extends Constraint
{
    public $message = 'The code {{ code }} is already assigned to another workspace.';

    public function validatedBy()
    {
        return 'workspace_unique_code_validator';
    }
}
