<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Event;

use Symfony\Component\EventDispatcher\Event;
use Symfony\Component\HttpFoundation\Response;
use Claroline\CoreBundle\Entity\Resource\AbstractResource;

/**
 * Event dispatched by the resource controller when a custom action is asked on a resource.
 */
class DownloadResourceEvent extends Event
{
    private $resource;
    private $item;
    private $extension;

    /**
     * Constructor.
     *
     * @param int $resourceId
     */
    public function __construct(AbstractResource $resource)
    {
        $this->resource = $resource;
    }

    /**
     * Returns the id of the resource on which the action is to be taken.
     *
     * @return int
     */
    public function getResource()
    {
        return $this->resource;
    }

    /**
     * Sets the exported item.
     *
     * @param mixed $item
     */
    public function setItem($item)
    {
        $this->item = $item;
    }

    /**
     * Returns the response for the action.
     *
     * @return Response
     */
    public function getItem()
    {
        return $this->item;
    }

    public function setExtension($extension)
    {
        $this->extension = $extension;
    }

    public function getExtension()
    {
        return $this->extension;
    }
}
