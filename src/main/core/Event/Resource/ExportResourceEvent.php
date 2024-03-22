<?php

namespace Claroline\CoreBundle\Event\Resource;

use Claroline\AppBundle\API\Utils\FileBag;
use Claroline\CoreBundle\Entity\Resource\AbstractResource;
use Symfony\Contracts\EventDispatcher\Event;

class ExportResourceEvent extends Event
{
    private array $data = [];

    public function __construct(
        private readonly AbstractResource $resource,
        private readonly FileBag $fileBag
    ) {
    }

    public function getResource(): AbstractResource
    {
        return $this->resource;
    }

    public function setData(?array $data = []): void
    {
        $this->data = $data;
    }

    public function getData(): array
    {
        return $this->data;
    }

    public function addFile(string $path, string $file): void
    {
        $this->fileBag->add($path, $file);
    }

    public function getFileBag(): FileBag
    {
        return $this->fileBag;
    }
}
