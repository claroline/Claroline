<?php

namespace Claroline\AppBundle\API\Transfer\Action;

use Claroline\AppBundle\API\Crud;
use Claroline\AppBundle\API\SerializerProvider;
use Claroline\AppBundle\API\TransferProvider;
use JMS\DiExtraBundle\Annotation as DI;

abstract class AbstractCreateOrUpdateAction extends AbstractAction
{
    abstract public function getClass();

    /**
     * Action constructor.
     *
     * @DI\InjectParams({
     *     "crud" = @DI\Inject("claroline.api.crud"),
     *     "serializer" = @DI\Inject("claroline.api.serializer"),
     *     "transfer" = @DI\Inject("claroline.api.transfer")
     * })
     *
     * @param Crud $crud
     */
    public function __construct(Crud $crud, SerializerProvider $serializer, TransferProvider $transfer)
    {
        $this->crud = $crud;
        $this->serializer = $serializer;
        $this->transfer = $transfer;
    }

    public function execute(array $data, &$successData = [])
    {
        //search the object. It'll look for the 1st identifier it finds so be carreful
        $object = $this->serializer->deserialize($this->getClass(), $data);
        $serializedclass = $this->getAction()[0];
        $action = !$object->getId() ? self::MODE_CREATE : self::MODE_UPDATE;
        $action = $serializedclass.'_'.$action;
        //finds and fire the action
        return $this->transfer->getExecutor($action)->execute($data, $successData);
    }

    public function getSchema()
    {
        return ['$root' => $this->getClass()];
    }
}
