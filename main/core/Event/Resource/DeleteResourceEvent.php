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
use Symfony\Component\EventDispatcher\Event;

/**
 * Event dispatched by the resource controller when a resource deletion is asked.
 */
class DeleteResourceEvent extends Event
{
    /** @var AbstractResource */
    private $resource;
    private $files = [];
    private $softDelete;

    /**
     * DeleteResourceEvent constructor.
     *
     * @param AbstractResource $resource
     * @param bool             $softDelete
     */
    public function __construct(AbstractResource $resource, $softDelete = false)
    {
        $this->resource = $resource;
        $this->softDelete = $softDelete;
    }

    /**
     * @return AbstractResource
     */
    public function getResource()
    {
        return $this->resource;
    }

    /**
     * Set an array of files which are going to be removed by the kernel.
     *
     * @param array $files
     */
    public function setFiles(array $files)
    {
        $this->files = $files;
    }

    public function getFiles()
    {
        return $this->files;
    }

    public function enableSoftDelete()
    {
        $this->softDelete = true;
    }

    public function isSoftDelete()
    {
        return $this->softDelete;
    }
}
