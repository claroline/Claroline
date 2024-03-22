<?php

namespace Claroline\CoreBundle\Event\Resource;

use Claroline\AppBundle\API\Utils\FileBag;
use Claroline\CoreBundle\Entity\Resource\AbstractResource;
use Symfony\Contracts\EventDispatcher\Event;

class ImportResourceEvent extends Event
{
    public function __construct(
        private readonly AbstractResource $resource,
        private readonly FileBag $fileBag,
        private readonly ?array $data = []
    ) {
    }

    public function getResource(): AbstractResource
    {
        return $this->resource;
    }

    public function getFileBag(): FileBag
    {
        return $this->fileBag;
    }

    public function getData(): array
    {
        return $this->data;
    }
}
