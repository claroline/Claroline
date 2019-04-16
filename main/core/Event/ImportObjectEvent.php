<?php

namespace Claroline\CoreBundle\Event;

use Claroline\AppBundle\API\Utils\FileBag;
use Claroline\CoreBundle\Entity\Workspace\Workspace;
use Symfony\Component\EventDispatcher\Event;

class ImportObjectEvent extends Event
{
    /**
     * TODO: write doc.
     */
    public function __construct(FileBag $fileBag = null, array $data = [], $object = null, $extra = null, Workspace $workspace = null)
    {
        $this->fileBag = $fileBag;
        $this->data = $data;
        $this->object = $object;
        $this->extra = $extra;
        $this->workspace = $workspace;
    }

    public function getFileBag()
    {
        return $this->fileBag;
    }

    public function getData()
    {
        return $this->data;
    }

    public function setData($data)
    {
        $this->data = $data;
    }

    public function getObject()
    {
        return $this->object;
    }

    public function setExtra($extra)
    {
        $this->extra = $extra;
    }

    public function getExtra()
    {
        return $this->extra;
    }

    public function getWorkspace()
    {
        return $this->workspace;
    }
}
