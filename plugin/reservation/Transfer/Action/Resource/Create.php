<?php

namespace FormaLibre\ReservationBundle\Transfer\Action\Resource;

use Claroline\CoreBundle\API\Crud;
use Claroline\CoreBundle\API\Transfer\Action\AbstractAction;
use Claroline\CoreBundle\Persistence\ObjectManager;
use JMS\DiExtraBundle\Annotation as DI;

/**
 * @DI\Service()
 * @DI\Tag("claroline.transfer.action")
 */
class Create extends AbstractAction
{
    /**
     * Action constructor.
     *
     * @DI\InjectParams({
     *     "crud" = @DI\Inject("claroline.api.crud")
     * })
     *
     * @param Crud $crud
     */
    public function __construct(Crud $crud)
    {
        $this->crud = $crud;
    }

    public function execute(array $data)
    {
        $this->crud->create('FormaLibre\ReservationBundle\Entity\Resource', $data);
    }

    public function getAction()
    {
        return ['reservation-resource', 'create'];
    }

    public function getBatchSize()
    {
        return 250;
    }

    public function clear(ObjectManager $om)
    {
    }

    public function getSchema()
    {
        return ['$root' => 'FormaLibre\ReservationBundle\Entity\Resource'];
    }
}
