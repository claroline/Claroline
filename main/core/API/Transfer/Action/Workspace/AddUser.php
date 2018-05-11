<?php

namespace Claroline\CoreBundle\API\Transfer\Action\Workspace;

use Claroline\AppBundle\API\Crud;
use Claroline\AppBundle\API\SerializerProvider;
use Claroline\AppBundle\API\Transfer\Action\AbstractAction;
use Claroline\AppBundle\Persistence\ObjectManager;
use JMS\DiExtraBundle\Annotation as DI;

/**
 * @DI\Service()
 * @DI\Tag("claroline.transfer.action")
 */
class AddUser extends AbstractAction
{
    /**
     * Action constructor.
     *
     * @DI\InjectParams({
     *     "crud"       = @DI\Inject("claroline.api.crud"),
     *     "serializer" = @DI\Inject("claroline.api.serializer"),
     *     "om"         = @DI\Inject("claroline.persistence.object_manager")
     * })
     *
     * @param Crud $crud
     */
    public function __construct(Crud $crud, SerializerProvider $serializer, ObjectManager $om)
    {
        $this->crud = $crud;
        $this->serializer = $serializer;
        $this->om = $om;
    }

    public function execute(array $data, &$successData = [])
    {
        $user = $this->serializer->deserialize(
            'Claroline\CoreBundle\Entity\User',
            $data['user']
        );

        if (!$user->getId()) {
            throw new \Exception('User '.$this->printError($data['user'])." doesn't exists.");
        }

        $workspace = $this->serializer->deserialize(
            'Claroline\CoreBundle\Entity\Workspace\Workspace',
            $data['workspace']
        );

        if (!$workspace->getId()) {
            throw new \Exception('Workspace '.$this->printError($data['workspace'])." doesn't exists.");
        }

        $role = $this->om->getRepository('ClarolineCoreBundle:Role')
          ->findOneBy(['workspace' => $workspace, 'translationKey' => $data['role']['translationKey']]);

        if (!$role->getId()) {
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

    public function getSchema()
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
            'class' => 'Claroline\CoreBundle\Entity\Role',
          ],
        ];

        $schema = json_decode(json_encode($roleSchema));

        return [
          'workspace' => 'Claroline\CoreBundle\Entity\Workspace\Workspace',
          'user' => 'Claroline\CoreBundle\Entity\User',
          'role' => $schema,
        ];
    }

    public function getAction()
    {
        return ['workspace', 'add_user'];
    }
}
