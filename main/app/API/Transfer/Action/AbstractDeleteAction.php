<?php

namespace Claroline\AppBundle\API\Transfer\Action;

use Claroline\AppBundle\API\Crud;
use Claroline\AppBundle\API\SerializerProvider;
use JMS\DiExtraBundle\Annotation as DI;

abstract class AbstractDeleteAction extends AbstractAction
{
    abstract public function getClass();

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
    public function __construct(Crud $crud, SerializerProvider $serializer, $om)
    {
        $this->crud = $crud;
        $this->serializer = $serializer;
        $this->om = $om;
    }

    public function execute(array $data, &$successData = [])
    {
        $object = $this->om->getObject($data[$this->getAction()[0]], $this->getClass());

        if ($object->getId()) {
            $this->crud->delete($object);

            $successData['delete'][] = [
                'data' => $data,
            ];
        }
    }

    public function getSchema(array $options = [], array $extra = [])
    {
        //this is so we don't show all properties. See TransferProvider and search $root
        return [$this->getAction()[0] => $this->getClass()];
    }
}
