<?php

namespace Claroline\CoreBundle\API\Validator;

use Claroline\AppBundle\API\ValidatorInterface;
use Claroline\CoreBundle\Entity\Workspace\Workspace;

class WorkspaceValidator implements ValidatorInterface
{
    public function validate($data, $mode, array $options = [])
    {
        return [];
    }

    public static function getClass(): string
    {
        return Workspace::class;
    }

    public function getUniqueFields()
    {
        return [
            'code' => 'code',
        ];
    }
}
