<?php

namespace Claroline\CommunityBundle\Validator;

use Claroline\AppBundle\API\ValidatorInterface;
use Claroline\CoreBundle\Entity\Role;

class RoleValidator implements ValidatorInterface
{
    public function validate(array $data, string $mode, array $options = []): array
    {
        return [];
    }

    public static function getClass(): string
    {
        return Role::class;
    }

    public function getUniqueFields(): array
    {
        return [
            'name' => 'name',
        ];
    }
}
