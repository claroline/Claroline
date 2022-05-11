<?php

namespace Claroline\CommunityBundle\Validator;

use Claroline\AppBundle\API\ValidatorInterface;
use Claroline\CoreBundle\Entity\Role;

class RoleValidator implements ValidatorInterface
{
    public function validate($data, $mode, array $options = [])
    {
        return [];
    }

    public static function getClass(): string
    {
        return Role::class;
    }

    public function getUniqueFields()
    {
        return [
            'id' => 'uuid',
            'name' => 'name',
        ];
    }
}
