<?php

namespace Claroline\CoreBundle\Event;

use Claroline\AppBundle\API\Utils\ArrayUtils;
use Claroline\AppBundle\API\Utils\FileBag;
use Claroline\CoreBundle\Entity\Resource\ResourceNode;
use Claroline\CoreBundle\Entity\Workspace\Workspace;
use Symfony\Contracts\EventDispatcher\Event;

class ExportObjectEvent extends Event
{
    /** @var mixed */
    protected $object;

    /** @var FileBag */
    protected $fileBag;

    /** @var array */
    protected $data;

    /**
     * ExportObjectEvent constructor.
     *
     * @param mixed $object
     */
    public function __construct(
        $object,
        FileBag $fileBag,
        array $data = [],
        Workspace $workspace = null
    ) {
        $this->object = $object;
        $this->data = $data;
        $this->fileBag = $fileBag;
        $this->workspace = $workspace;
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

    public function getWorkspace()
    {
        return $this->workspace;
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
        ArrayUtils::set($this->data, $key, $value);
    }
}
