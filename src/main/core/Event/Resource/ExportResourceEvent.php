<?php

namespace Claroline\CoreBundle\Event\Resource;

use Claroline\AppBundle\API\Utils\FileBag;
use Claroline\CoreBundle\Entity\Resource\AbstractResource;
use Symfony\Contracts\EventDispatcher\Event;

class ExportResourceEvent extends Event
{
    /** @var AbstractResource */
    private $resource;
    /** @var FileBag */
    private $fileBag;
    /** @var array */
    private $data = [];

    public function __construct(
        AbstractResource $resource,
        FileBag $fileBag
    ) {
        $this->resource = $resource;
        $this->fileBag = $fileBag;
    }

    public function getResource(): AbstractResource
    {
        return $this->resource;
    }

    public function setData(?array $data = [])
    {
        $this->data = $data;
    }

    public function getData(): array
    {
        return $this->data;
    }

    public function addFile(string $path, string $file)
    {
        $this->fileBag->add($path, $file);
    }

    public function getFileBag(): FileBag
    {
        return $this->fileBag;
    }
}
