<?php

namespace Claroline\CommunityBundle\Transfer\Importer\Group;

use Claroline\AppBundle\API\Crud;
use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Entity\Group;
use Claroline\CoreBundle\Entity\User;
use Claroline\TransferBundle\Transfer\Importer\AbstractImporter;

class RemoveUser extends AbstractImporter
{
    /** @var Crud */
    private $crud;
    /** @var ObjectManager */
    private $om;

    public function __construct(Crud $crud, ObjectManager $om)
    {
        $this->crud = $crud;
        $this->om = $om;
    }

    public function execute(array $data): array
    {
        /** @var User $user */
        $user = $this->om->getObject($data['user'], User::class, array_keys($data['user']));
        /** @var Group $group */
        $group = $this->om->getObject($data['group'], Group::class, array_keys($data['group']));

        if ($user && $group && $user->hasGroup($group)) {
            $this->crud->patch($user, 'group', 'remove', [$group]);

            return [
                'remove_user' => [[
                    'data' => $data,
                    'log' => 'user removed from group.',
                ]],
            ];
        }

        return [];
    }

    public function getSchema(?array $options = [], ?array $extra = []): array
    {
        return ['group' => Group::class, 'user' => User::class];
    }

    /**
     * return an array with the following element:
     * - section
     * - action
     * - action name.
     */
    public function getAction(): array
    {
        return ['group', 'remove_user'];
    }
}
