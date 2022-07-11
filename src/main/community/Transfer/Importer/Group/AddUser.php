<?php

namespace Claroline\CommunityBundle\Transfer\Importer\Group;

use Claroline\AppBundle\API\Crud;
use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Entity\Group;
use Claroline\CoreBundle\Entity\User;
use Claroline\TransferBundle\Transfer\Importer\AbstractImporter;

class AddUser extends AbstractImporter
{
    /** @var Crud */
    private $crud;
    /** @var ObjectManager */
    private $om;

    /**
     * AddUser constructor.
     */
    public function __construct(Crud $crud, ObjectManager $om)
    {
        $this->crud = $crud;
        $this->om = $om;
    }

    public function execute(array $data): array
    {
        /** @var User $user */
        $user = $this->om->getObject($data['user'], User::class, array_keys($data['user']));
        if (!$user) {
            throw new \Exception('User does not exists');
        }

        /** @var Group $group */
        $group = $this->om->getObject($data['group'], Group::class, array_keys($data['group']));
        if (!$group) {
            throw new \Exception('Group does not exists');
        }

        if (!$user->hasGroup($group)) {
            $this->crud->patch($group, 'user', 'add', [$user]);

            return [
                'add_user' => [[
                    'data' => $data,
                    'log' => 'user registered to group.',
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
    public static function getAction(): array
    {
        return ['group', 'add_user'];
    }
}
