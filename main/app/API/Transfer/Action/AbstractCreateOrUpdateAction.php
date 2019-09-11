<?php

namespace Claroline\AppBundle\API\Transfer\Action;

use Claroline\AppBundle\API\Crud;
use Claroline\AppBundle\API\SerializerProvider;
use Claroline\AppBundle\API\TransferProvider;
use Claroline\AppBundle\Persistence\ObjectManager;

abstract class AbstractCreateOrUpdateAction extends AbstractAction
{
    abstract public function getClass();

    public function setCrud(Crud $crud)
    {
        $this->crud = $crud;
    }

    public function setSerializer(SerializerProvider $serializer)
    {
        $this->serializer = $serializer;
    }

    public function setTransfer(TransferProvider $transfer)
    {
        $this->transfer = $transfer;
    }

    public function setObjectManager(ObjectManager $om)
    {
        $this->om = $om;
    }

    public function execute(array $data, &$successData = [])
    {
        //search the object. It'll look for the 1st identifier it finds so be carreful
        $object = $this->om->getObject($data, $this->getClass()) ?? new $this->getClass();
        $object = $this->serializer->deserialize($data, $object);
        $serializedclass = $this->getAction()[0];
        $action = !$object->getId() ? self::MODE_CREATE : self::MODE_UPDATE;
        $action = $serializedclass.'_'.$action;
        //finds and fire the action
        return $this->transfer->getExecutor($action)->execute($data, $successData);
    }

    public function getSchema(array $options = [], array $extra = [])
    {
        return ['$root' => $this->getClass()];
    }
}
