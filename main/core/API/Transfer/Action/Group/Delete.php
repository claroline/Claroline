<?php

namespace Claroline\CoreBundle\API\Transfer\Action\Group;

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
        $group = $this->serializer->deserialize(
            'Claroline\CoreBundle\Entity\Group',
            $data->group[0]
        );

        $this->crud->delete('Claroline\CoreBundle\Entity\Group', $group);
    }

    public function getSchema()
    {
        return ['group' => 'Claroline\CoreBundle\Entity\Group'];
    }

    /**
     * return an array with the following element:
     * - section
     * - action
     * - action name.
     */
    public function getAction()
    {
        return ['group', 'delete'];
    }

    public function getBatchSize()
    {
        return 100;
    }

    public function clear(ObjectManager $om)
    {
    }
}
