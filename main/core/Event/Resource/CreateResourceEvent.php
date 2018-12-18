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
use Claroline\CoreBundle\Entity\Resource\ResourceNode;
use Symfony\Component\EventDispatcher\Event;

/**
 * Event dispatched when a resource creation is asked.
 */
class CreateResourceEvent extends Event
{
    /** @var AbstractResource */
    private $resource;

    /**
     * CreateResourceEvent constructor.
     *
     * @param AbstractResource $resource
     */
    public function __construct(AbstractResource $resource)
    {
        $this->resource = $resource;
    }

    /**
     * Gets the resource ResourceNode entity.
     *
     * @return ResourceNode
     */
    public function getResourceNode()
    {
        return $this->resource->getResourceNode();
    }

    public function getResource()
    {
        return $this->resource;
    }

    public function setResource(AbstractResource $resource)
    {
        $this->resource = $resource;
    }
}
