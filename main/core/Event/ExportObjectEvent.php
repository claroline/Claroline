<?php

namespace Claroline\CoreBundle\Event;

use Claroline\AppBundle\API\Utils\ArrayUtils;
use Claroline\AppBundle\API\Utils\FileBag;
use Claroline\CoreBundle\Entity\Resource\ResourceNode;
use Symfony\Component\EventDispatcher\Event;

class ExportObjectEvent extends Event
{
    /**
     * TODO: write doc.
     */
    public function __construct(
        $object,
        FileBag $fileBag,
        array $data = []
    ) {
        $this->object = $object;
        $this->data = $data;
        $this->fileBag = $fileBag;
        $this->arrayUtils = new ArrayUtils();
    }

    /**
     * Gets the resource node being serialized.
     *
     * @return ResourceNode
     */
    public function getObject()
    {
        return $this->object;
    }

    public function setData($data)
    {
        $this->data = $data;
    }

    public function getData()
    {
        return $this->data;
    }

    public function addFile($path, $file)
    {
        $this->fileBag->add($path, $file);
    }

    public function getFileBag()
    {
        return $this->fileBag;
    }

    public function overwrite($key, $value)
    {
        $this->arrayUtils->set($this->data, $key, $value);
    }
}
