<?php

namespace Claroline\CoreBundle\Transfer\Importer\Workspace;

use Claroline\AppBundle\API\Crud;
use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Entity\Group;
use Claroline\CoreBundle\Entity\Role;
use Claroline\CoreBundle\Entity\Workspace\Workspace;
use Claroline\TransferBundle\Transfer\Importer\AbstractImporter;

class RemoveAllGroups extends AbstractImporter
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
        if (!isset($data['workspace'])) {
            throw new \InvalidArgumentException('The "workspace" key is missing or is not an array.');
        }

        $workspace = $this->om->getRepository(Workspace::class)->findOneBy($data['workspace']);

        if (!$workspace) {
            throw new \Exception('Workspace '.$this->printError($data['workspace'])." doesn't exists.");
        }

        $groups = $this->om->getRepository(Group::class)->findByWorkspace($workspace);

        foreach ($groups as $group) {
            $role = $this->om->getRepository(Role::class)
                ->findOneBy(['workspace' => $workspace, 'translationKey' => $data['role']['translationKey']]);

            if (!$role) {
                throw new \Exception('Role '.$this->printError($data['role'])." doesn't exists.");
            }

            $this->crud->patch($group, 'role', 'remove', [$role]);
        }

        return [];
    }

    private function printError(array $el): string
    {
        $string = '';

        foreach ($el as $value) {
            $string .= ' '.$value;
        }

        return $string;
    }

    public function getSchema(?array $options = [], ?array $extra = []): array
    {
        return [
            'workspace' => Workspace::class,
            'role' => json_decode(json_encode([
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
            ])),
        ];
    }

    public static function getAction(): array
    {
        return ['workspace', 'remove_all_groups'];
    }

    public function supports(string $format, ?array $options = [], ?array $extra = []): bool
    {
        return in_array($format, ['json', 'csv']);
    }
}
