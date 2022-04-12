<?php

namespace Claroline\CoreBundle\Event\Resource;

use Claroline\AppBundle\API\Utils\FileBag;
use Claroline\CoreBundle\Entity\Resource\AbstractResource;
use Symfony\Contracts\EventDispatcher\Event;

class ImportResourceEvent extends Event
{
    /** @var AbstractResource */
    private $resource;
    /** @var FileBag */
    private $fileBag;
    /** @var array */
    private $data;

    public function __construct(
        AbstractResource $resource,
        FileBag $fileBag,
        ?array $data = []
    ) {
        $this->fileBag = $fileBag;
        $this->data = $data;
        $this->resource = $resource;
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
