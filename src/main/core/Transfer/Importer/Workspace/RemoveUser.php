<?php

namespace Claroline\CoreBundle\Transfer\Importer\Workspace;

use Claroline\AppBundle\API\Crud;
use Claroline\AppBundle\API\Options;
use Claroline\AppBundle\API\SerializerProvider;
use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Entity\Role;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Entity\Workspace\Workspace;
use Claroline\TransferBundle\Transfer\Importer\AbstractImporter;

class RemoveUser extends AbstractImporter
{
    /** @var Crud */
    private $crud;
    /** @var SerializerProvider */
    private $serializer;
    /** @var ObjectManager */
    private $om;

    public function __construct(Crud $crud, SerializerProvider $serializer, ObjectManager $om)
    {
        $this->crud = $crud;
        $this->serializer = $serializer;
        $this->om = $om;
    }

    public function execute(array $data): array
    {
        // this should be handled by csv validation
        if (!isset($data['role'])) {
            throw new \Exception('No role set for delete for user '.$this->printError($data['user']).'.');
        }

        $user = $this->om->getObject($data['user'], User::class, array_keys($data['user']));

        if (!$user) {
            throw new \Exception('User '.$this->printError($data['user'])." doesn't exists.");
        }

        //todo find a generic way to find the identifiers
        $workspace = $this->om->getObject($data['workspace'], Workspace::class, ['code']);

        if (!$workspace) {
            throw new \Exception('Workspace '.$this->printError($data['workspace'])." doesn't exists.");
        }

        $role = $this->om->getRepository(Role::class)
            ->findOneBy(['workspace' => $workspace, 'translationKey' => $data['role']['translationKey']]);

        if (!$role) {
            throw new \Exception('Role '.$this->printError($data['role'])." doesn't exists.");
        }

        $this->crud->patch($user, 'role', 'remove', [$role]);

        return [];
    }

    public function printError(array $el)
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

        $schema = [
          'user' => User::class,
          'role' => $schema,
        ];

        if (!in_array(Options::WORKSPACE_IMPORT, $options)) {
            $schema['workspace'] = Workspace::class;
        }

        return $schema;
    }

    public function getAction(): array
    {
        return ['workspace', 'remove_user'];
    }

    public function supports(string $format, ?array $options = [], ?array $extra = []): bool
    {
        return in_array($format, ['json', 'csv']);
    }
}
