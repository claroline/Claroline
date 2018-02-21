<?php

namespace Claroline\CoreBundle\API\Transfer\Action\User;

use Claroline\AppBundle\API\Crud;
use Claroline\AppBundle\API\SerializerProvider;
use Claroline\AppBundle\API\Transfer\Action\AbstractAction;
use Claroline\AppBundle\Persistence\ObjectManager;
use JMS\DiExtraBundle\Annotation as DI;

/**
 * @DI\Service()
 * @DI\Tag("claroline.transfer.action")
 */
class Delete extends AbstractAction
{
    /**
     * Action constructor.
     *
     * @DI\InjectParams({
     *     "crud"       = @DI\Inject("claroline.api.crud"),
     *     "serializer" = @DI\Inject("claroline.api.serializer")
     * })
     *
     * @param Crud $crud
     */
    public function __construct(Crud $crud, SerializerProvider $serializer)
    {
        $this->crud = $crud;
        $this->serializer = $serializer;
    }

    public function execute(array $data)
    {
        $user = $this->serializer->deserialize(
            'Claroline\CoreBundle\Entity\User',
            $data->user[0]
        );

        $this->crud->delete('Claroline\CoreBundle\Entity\User', $user);
    }

    public function getSchema()
    {
        return ['user' => 'Claroline\CoreBundle\Entity\User'];
    }

    /**
     * return an array with the following element:
     * - section
     * - action
     * - action name.
     */
    public function getAction()
    {
        return ['user', 'delete'];
    }

    public function getBatchSize()
    {
        return 100;
    }

    public function clear(ObjectManager $om)
    {
    }
}
