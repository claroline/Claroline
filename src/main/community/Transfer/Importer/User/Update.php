<?php

namespace Claroline\CommunityBundle\Transfer\Importer\User;

use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Entity\Group;
use Claroline\CoreBundle\Entity\Organization\Organization;
use Claroline\CoreBundle\Entity\Role;
use Claroline\CoreBundle\Entity\User;
use Claroline\TransferBundle\Transfer\Importer\AbstractUpdateImporter;

class Update extends AbstractUpdateImporter
{
    /** @var ObjectManager */
    private $om;

    public function __construct(ObjectManager $om)
    {
        $this->om = $om;
    }

    public static function getAction(): array
    {
        return ['user', self::MODE_UPDATE];
    }

    public function execute(array $data): array
    {
        $groups = [];
        if (isset($data['groups'])) {
            foreach ($data['groups'] as $group) {
                $object = $this->om->getObject($group, Group::class, array_keys($group));
                if (!$object) {
                    throw new \Exception('Group '.implode(',', $group).' does not exists');
                }

                $groups[] = $object;
            }

            // remove groups from input data to avoid the user serializer to process it
            unset($data['groups']);
        }

        $roles = [];
        if (isset($data['roles'])) {
            foreach ($data['roles'] as $role) {
                $object = $this->om->getObject($role, Role::class, array_keys($role));
                if (!$object) {
                    throw new \Exception('Role '.implode(',', $role).' does not exists');
                }

                $roles[] = $object;
            }

            // remove roles from input data to avoid the user serializer to process it
            unset($data['roles']);
        }

        $organizations = [];
        if (isset($data['organizations'])) {
            foreach ($data['organizations'] as $organization) {
                $object = $this->om->getObject($organization, Organization::class, array_keys($organization));
                if (!$object) {
                    throw new \Exception('Organization '.implode(',', $organization).' does not exists');
                }

                $organizations[] = $object;
            }

            // remove organizations from input data to avoid the user serializer to process it
            unset($data['organizations']);
        }

        $user = $this->crud->update(static::getClass(), $data);
        if ($user) {
            if (!empty($groups)) {
                $this->crud->patch($user, 'group', 'add', $groups);
            }

            if (!empty($roles)) {
                $this->crud->patch($user, 'role', 'add', $roles);
            }

            if (!empty($organizations)) {
                $this->crud->patch($user, 'organization', 'add', $organizations);
            }
        }

        return [
            'update' => [[
                'data' => $data,
                'log' => static::getAction()[0].' updated.',
            ]],
        ];
    }

    protected static function getClass(): string
    {
        return User::class;
    }
}
