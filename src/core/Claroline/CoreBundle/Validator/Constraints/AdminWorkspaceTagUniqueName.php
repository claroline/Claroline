<?php

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