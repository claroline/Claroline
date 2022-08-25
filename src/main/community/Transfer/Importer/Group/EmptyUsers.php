<?php

namespace Claroline\CommunityBundle\Transfer\Importer\Group;

use Claroline\AppBundle\API\Crud;
use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Entity\Group;
use Claroline\TransferBundle\Transfer\Importer\AbstractImporter;

class EmptyUsers extends AbstractImporter
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
        /** @var Group $group */
        $group = $this->om->getObject($data['group'], Group::class, array_keys($data['group']));

        if ($group) {
            $this->crud->patch($group, 'user', 'remove', $group->getUsers()->toArray());

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

    /**
     * return an array with the following element:
     * - section
     * - action
     * - action name.
     */
    public static function getAction(): array
    {
        return ['group', 'empty_users'];
    }
}
