<?php

namespace Claroline\CoreBundle\Transfer\Importer\Workspace;

use Claroline\AppBundle\API\Crud;
use Claroline\AppBundle\API\Options;
use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Entity\Role;
use Claroline\CoreBundle\Entity\Workspace\Workspace;
use Claroline\TransferBundle\Transfer\Importer\AbstractImporter;

class EmptyRole extends AbstractImporter
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
        if (empty($data['role']) || empty($data['role']['translationKey'])) {
            throw new \Exception('Missing role.translationKey.');
        }

        $workspace = $this->om->getObject($data['workspace'], Workspace::class, array_keys($data['workspace']));
        if (empty($workspace)) {
            throw new \Exception('Workspace not found.');
        }

        /** @var Role $role */
        $role = $this->om->getRepository(Role::class)->findOneBy([
            'workspace' => $workspace,
            'translationKey' => $data['role']['translationKey'],
        ]);

        if ($role) {
            $this->crud->patch($role, 'user', 'remove', $role->getUsers()->toArray());
            $this->crud->patch($role, 'group', 'remove', $role->getGroups()->toArray());

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

    /**
     * return an array with the following element:
     * - section
     * - action
     * - action name.
     */
    public static function getAction(): array
    {
        return ['workspace', 'empty_role'];
    }
}
