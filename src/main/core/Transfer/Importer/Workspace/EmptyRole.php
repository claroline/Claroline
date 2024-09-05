<?php

namespace Claroline\CoreBundle\Transfer\Importer\Workspace;

use Claroline\AppBundle\API\Crud;
use Claroline\AppBundle\API\Options;
use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Entity\Group;
use Claroline\CoreBundle\Entity\Role;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Entity\Workspace\Workspace;
use Claroline\TransferBundle\Transfer\Importer\AbstractImporter;

class EmptyRole extends AbstractImporter
{
    public function __construct(
        private readonly Crud $crud,
        private readonly ObjectManager $om
    ) {
    }

    public static function getAction(): array
    {
        return ['workspace', 'empty_role'];
    }

    public function execute(array $data): array
    {
        if (empty($data['role']) || empty($data['role']['translationKey'])) {
            throw new \Exception('Missing role.translationKey.');
        }

        $workspace = $this->crud->find(Workspace::class, $data['workspace']);
        if (empty($workspace)) {
            throw new \Exception('Workspace not found.');
        }

        /** @var Role $role */
        $role = $this->om->getRepository(Role::class)->findOneBy([
            'workspace' => $workspace,
            'translationKey' => $data['role']['translationKey'],
        ]);

        if ($role) {
            $groups = $this->om->getRepository(Group::class)->findByRole($role);
            $users = $this->om->getRepository(User::class)->findByRoles([$role], false);

            $this->crud->patch($role, 'user', 'remove', $users);
            $this->crud->patch($role, 'group', 'remove', $groups);

            return [
                'empty_role' => [[
                    'data' => $data,
                    'log' => 'all users and groups removed from role.',
                ]],
            ];
        }

        return [];
    }

    public function getSchema(?array $options = [], ?array $extra = []): array
    {
        $roleSchema = [
            '$schema' => 'http:\/\/json-schema.org\/draft-04\/schema#',
            'type' => 'object',
            'properties' => [
                'translationKey' => [
                    'type' => 'string',
                    'description' => 'The role name',
                ],
            ],
            'claroline' => [
                'requiredAtCreation' => ['translationKey'],
                'ids' => ['translationKey'],
                'class' => Role::class,
            ],
        ];

        $schema = [
            'role' => json_decode(json_encode($roleSchema)),
        ];

        if (!in_array(Options::WORKSPACE_IMPORT, $options)) {
            $schema['workspace'] = Workspace::class;
        }

        return $schema;
    }
}
