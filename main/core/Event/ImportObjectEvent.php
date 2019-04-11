<?php

namespace Claroline\CoreBundle\Event;

use Claroline\AppBundle\API\Utils\FileBag;
use Symfony\Component\EventDispatcher\Event;

class ImportObjectEvent extends Event
{
    /**
     * TODO: write doc.
     */
    public function __construct(FileBag $fileBag = null, array $data = [], $object = null)
    {
        $this->fileBag = $fileBag;
        $this->data = $data;
        $this->object = $object;
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
}
