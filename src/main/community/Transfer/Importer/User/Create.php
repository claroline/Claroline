<?php

namespace Claroline\CommunityBundle\Transfer\Importer\User;

use Claroline\AppBundle\API\Crud;
use Claroline\AppBundle\API\Options;
use Claroline\CoreBundle\Entity\Group;
use Claroline\CoreBundle\Entity\Organization\Organization;
use Claroline\CoreBundle\Entity\Role;
use Claroline\CoreBundle\Entity\User;
use Claroline\TransferBundle\Transfer\Importer\AbstractImporter;

final class Create extends AbstractImporter
{
    public function __construct(
        private readonly Crud $crud
    ) {
    }

    public static function getAction(): array
    {
        return ['user', self::MODE_CREATE];
    }

    public function execute(array $data): array
    {
        $hasWs = false;
        $options = [Options::FORCE_FLUSH];

        if (isset($data['meta']) && isset($data['meta']['personalWorkspace'])) {
            $hasWs = $data['meta']['personalWorkspace'];
        }

        if (!$hasWs) {
            $options[] = Options::NO_PERSONAL_WORKSPACE;
        }

        if (isset($data['mainOrganization'])) {
            $organization = $this->crud->find(Organization::class, $data['mainOrganization']);
            if (!$organization) {
                throw new \Exception('Organization '.implode(',', $data['mainOrganization']).' does not exists');
            }
        }

        $groups = [];
        if (isset($data['groups'])) {
            foreach ($data['groups'] as $group) {
                $object = $this->crud->find(Group::class, $group);
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
                $object = $this->crud->find(Role::class, $role);
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
                $object = $this->crud->find(Organization::class, $organization);
                if (!$object) {
                    throw new \Exception('Organization '.implode(',', $organization).' does not exists');
                }

                $organizations[] = $object;
            }

            // remove organizations from input data to avoid the user serializer to process it
            unset($data['organizations']);
        }

        $user = $this->crud->create(User::class, $data, $options);
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
            'create' => [[
                'data' => $data,
                'log' => static::getAction()[0].' created.',
            ]],
        ];
    }

    public function getSchema(?array $options = [], ?array $extra = []): array
    {
        return ['$root' => User::class];
    }

    public function getMode(): string
    {
        return self::MODE_CREATE;
    }
}
