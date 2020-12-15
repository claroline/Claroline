<?php

namespace Claroline\CoreBundle\API\Transfer\Action\Workspace;

use Claroline\AppBundle\API\Crud;
use Claroline\AppBundle\API\Options;
use Claroline\AppBundle\API\SerializerProvider;
use Claroline\AppBundle\API\Transfer\Action\AbstractAction;
use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Entity\Role;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Entity\Workspace\Workspace;

class AddUser extends AbstractAction
{
    /** @var ObjectManager */
    private $om;
    /** @var SerializerProvider */
    private $serializer;
    /** @var Crud */
    private $crud;

    /**
     * AddUser constructor.
     *
     * @param Crud               $crud
     * @param SerializerProvider $serializer
     * @param ObjectManager      $om
     */
    public function __construct(Crud $crud, SerializerProvider $serializer, ObjectManager $om)
    {
        $this->crud = $crud;
        $this->serializer = $serializer;
        $this->om = $om;
    }

    public function execute(array $data, &$successData = [])
    {
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

        $this->crud->patch($user, 'role', 'add', [$role]);
    }

    public function printError(array $el)
    {
        $string = '';

        foreach ($el as $value) {
            $string .= ' '.$value;
        }

        return $string;
    }

    public function getSchema(array $options = [], array $extra = [])
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

    public function getAction()
    {
        return ['workspace', 'add_user'];
    }

    public function supports($format, array $options = [], array $extra = [])
    {
        return in_array($format, ['json', 'csv']);
    }
}
