<?php

namespace Claroline\CoreBundle\Transfer\Importer\Workspace;

use Claroline\AppBundle\API\Crud;
use Claroline\AppBundle\API\Options;
use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Entity\Role;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Entity\Workspace\Workspace;
use Claroline\TransferBundle\Transfer\Importer\AbstractImporter;

class AddUser extends AbstractImporter
{
    public function __construct(
        private readonly Crud $crud,
        private readonly ObjectManager $om
    ) {
    }

    public function execute(array $data): array
    {
        $user = $this->crud->find(User::class, $data['user']);
        if (!$user) {
            throw new \Exception('User '.$this->printError($data['user'])." doesn't exists.");
        }

        $workspace = $this->crud->find(Workspace::class, $data['workspace']);
        if (!$workspace) {
            throw new \Exception('Workspace '.$this->printError($data['workspace'])." doesn't exists.");
        }

        $role = $this->om->getRepository(Role::class)
          ->findOneBy(['workspace' => $workspace, 'translationKey' => $data['role']['translationKey']]);

        if (!$role) {
            throw new \Exception('Role '.$this->printError($data['role'])." doesn't exists.");
        }

        $this->crud->patch($user, 'role', 'add', [$role]);

        return [];
    }

    public function printError(array $el): string
    {
        $string = '';

        foreach ($el as $value) {
            $string .= ' '.$value;
        }

        return $string;
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

        $schema = json_decode(json_encode($roleSchema));

        $schema = ['user' => User::class, 'role' => $schema];

        if (!in_array(Options::WORKSPACE_IMPORT, $options)) {
            $schema['workspace'] = Workspace::class;
        }

        return $schema;
    }

    public static function getAction(): array
    {
        return ['workspace', 'add_user'];
    }

    public function supports(string $format, ?array $options = [], ?array $extra = []): bool
    {
        return in_array($format, ['json', 'csv']);
    }
}
