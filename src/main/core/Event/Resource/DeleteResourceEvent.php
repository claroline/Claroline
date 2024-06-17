<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Event\Resource;

use Claroline\CoreBundle\Entity\Resource\AbstractResource;
use Symfony\Contracts\EventDispatcher\Event;

/**
 * Event dispatched by the resource controller when a resource deletion is asked.
 */
class DeleteResourceEvent extends Event
{
    private AbstractResource $resource;
    private array $files = [];
    private bool $softDelete;

    public function __construct(AbstractResource $resource, bool $softDelete = false)
    {
        $this->resource = $resource;
        $this->softDelete = $softDelete;
    }

    public function getResource(): AbstractResource
    {
        return $this->resource;
    }

    /**
     * Set an array of files which are going to be removed by the kernel.
     */
    public function setFiles(array $files): void
    {
        $this->files = $files;
    }

    public function getFiles(): array
    {
        return $this->files;
    }

    public function enableSoftDelete(): void
    {
        $this->softDelete = true;
    }

    public function isSoftDelete(): bool
    {
        return $this->softDelete;
    }
}
