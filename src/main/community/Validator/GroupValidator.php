<?php

namespace Claroline\CommunityBundle\Validator;

use Claroline\AppBundle\API\ValidatorInterface;
use Claroline\CoreBundle\Entity\Group;

class GroupValidator implements ValidatorInterface
{
    public function validate($data, $mode, array $options = []): array
    {
        return [];
    }

    public function getUniqueFields(): array
    {
        return [
            'code' => 'code',
        ];
    }

    public static function getClass(): string
    {
        return Group::class;
    }
}
