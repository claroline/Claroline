<?php

namespace Claroline\CommunityBundle\Transfer\Importer\Group;

use Claroline\AppBundle\API\Crud;
use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Entity\Group;
use Claroline\CoreBundle\Entity\User;
use Claroline\TransferBundle\Transfer\Importer\AbstractImporter;

class EmptyUsers extends AbstractImporter
{
    public function __construct(
        private readonly Crud $crud,
        private readonly ObjectManager $om
    ) {
    }

    public static function getAction(): array
    {
        return ['group', 'empty_users'];
    }

    public function execute(array $data): array
    {
        /** @var Group $group */
        $group = $this->crud->find(Group::class, $data['group']);

        if ($group) {
            $users = $this->om->getRepository(User::class)->findByGroup($group);
            $this->crud->patch($group, 'user', 'remove', $users);

            return [
                'empty_users' => [[
                    'data' => $data,
                    'log' => 'all users removed from group.',
                ]],
            ];
        }

        return [];
    }

    public function getSchema(?array $options = [], ?array $extra = []): array
    {
        return ['group' => Group::class];
    }
}
