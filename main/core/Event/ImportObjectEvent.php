<?php

namespace Claroline\CoreBundle\Event;

use Claroline\AppBundle\API\Utils\FileBag;
use Symfony\Component\EventDispatcher\Event;

class ImportObjectEvent extends Event
{
    /**
     * TODO: write doc.
     */
    public function __construct(FileBag $fileBag, array $data)
    {
        $this->fileBag = $fileBag;
        $this->data = $data;
    }

    public function getFileBag()
    {
        return $this->fileBag;
    }

    public function getData()
    {
        return $this->data;
    }
}
