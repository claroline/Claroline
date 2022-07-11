<?php

namespace Claroline\CoreBundle\Transfer\Importer\Workspace;

use Claroline\AppBundle\API\Crud;
use Claroline\AppBundle\API\Options;
use Claroline\AppBundle\API\SerializerProvider;
use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Entity\Group;
use Claroline\CoreBundle\Entity\Role;
use Claroline\CoreBundle\Entity\Workspace\Workspace;
use Claroline\TransferBundle\Transfer\Importer\AbstractImporter;

class AddGroup extends AbstractImporter
{
    /** @var ObjectManager */
    private $om;
    /** @var SerializerProvider */
    private $serializer;
    /** @var Crud */
    private $crud;

    public function __construct(Crud $crud, SerializerProvider $serializer, ObjectManager $om)
    {
        $this->crud = $crud;
        $this->serializer = $serializer;
        $this->om = $om;
    }

    public function execute(array $data): array
    {
        $group = $this->om->getObject($data['group'], Group::class, array_keys($data['group']));

        if (!$group) {
            throw new \Exception('Group '.$this->printError($data['group'])." doesn't exists.");
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

        $this->crud->patch($group, 'role', 'add', [$role]);

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

        $schema = ['group' => Group::class, 'role' => $schema];

        if (!in_array(Options::WORKSPACE_IMPORT, $options)) {
            $schema['workspace'] = Workspace::class;
        }

        return $schema;
    }

    public static function getAction(): array
    {
        return ['workspace', 'add_group'];
    }

    public function supports(string $format, ?array $options = [], ?array $extra = []): bool
    {
        return in_array($format, ['json', 'csv']);
    }
}
