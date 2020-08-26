<?php

namespace Claroline\AppBundle\API\Transfer\Action;

use Claroline\AppBundle\API\Crud;
use Claroline\AppBundle\API\SerializerProvider;
use Claroline\AppBundle\Persistence\ObjectManager;

abstract class AbstractDeleteAction extends AbstractAction
{
    /** @var ObjectManager */
    private $om;
    /** @var SerializerProvider */
    private $serializer;
    /** @var Crud */
    private $crud;

    abstract public function getClass();

    public function setCrud(Crud $crud)
    {
        $this->crud = $crud;
    }

    public function setSerializer(SerializerProvider $serializer)
    {
        $this->serializer = $serializer;
    }

    public function setObjectManager(ObjectManager $om)
    {
        $this->om = $om;
    }

    public function execute(array $data, &$successData = [])
    {
        $object = $this->om->getObject(
            $data[$this->getAction()[0]],
            $this->getClass(),
            array_keys($data[$this->getAction()[0]])
        );

        if (!empty($object)) {
            $this->crud->delete($object);

            $successData['delete'][] = [
                'data' => $data,
                'log' => $this->getAction()[0].' deleted.',
            ];
        }
    }

    public function getSchema(array $options = [], array $extra = [])
    {
        //this is so we don't show all properties. See TransferProvider and search $root
        return [$this->getAction()[0] => $this->getClass()];
    }
}
