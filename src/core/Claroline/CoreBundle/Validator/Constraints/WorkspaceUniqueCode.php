<?php

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