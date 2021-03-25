<?php

namespace Claroline\DropZoneBundle\Serializer;

use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\DropZoneBundle\Entity\DropzoneTool;

class DropzoneToolSerializer
{
    private $dropzoneToolRepo;

    /**
     * DropzoneToolSerializer constructor.
     */
    public function __construct(ObjectManager $om)
    {
        $this->dropzoneToolRepo = $om->getRepository('Claroline\DropZoneBundle\Entity\DropzoneTool');
    }

    /**
     * @return array
     */
    public function serialize(DropzoneTool $dropzoneTool)
    {
        return [
            'id' => $dropzoneTool->getUuid(),
            'name' => $dropzoneTool->getName(),
            'type' => $dropzoneTool->getType(),
            'data' => $dropzoneTool->getData(),
        ];
    }

    public function getName()
    {
        return 'dropzone_tool';
    }

    /**
     * Deserializes data into a Group entity.
     *
     * @param \stdClass    $data
     * @param DropzoneTool $dropzoneTool
     *
     * @return DropzoneTool
     */
    public function deserialize($data, DropzoneTool $dropzoneTool = null)
    {
        if (empty($dropzoneTool)) {
            $dropzoneTool = new DropzoneTool();
            $dropzoneTool->setUuid($data['id']);
        }
        if (isset($data['name'])) {
            $dropzoneTool->setName($data['name']);
        }
        if (isset($data['type'])) {
            $dropzoneTool->setType($data['type']);
        }
        if (isset($data['data'])) {
            $dropzoneTool->setData($data['data']);
        }

        return $dropzoneTool;
    }
}
