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

use Claroline\AppBundle\Event\MandatoryEventInterface;
use Claroline\CoreBundle\Entity\Resource\AbstractResource;
use Claroline\CoreBundle\Entity\Resource\ResourceNode;
use Symfony\Component\EventDispatcher\Event;

/**
 * A generic event occurring on a Resource.
 */
class ResourceEvent extends Event implements MandatoryEventInterface
{
    /** @var AbstractResource */
    private $resource;

    /** @var ResourceNode */
    private $resourceNode;

    /**
     * ResourceEvent constructor.
     *
     * @param AbstractResource $resource
     * @param ResourceNode     $resourceNode
     */
    public function __construct(AbstractResource $resource = null, ResourceNode $resourceNode = null)
    {
        $this->resource = $resource;
        $this->resourceNode = $resourceNode;
    }

    /**
     * @return AbstractResource
     */
    public function getResource()
    {
        return $this->resource;
    }

    /**
     * @return ResourceNode
     */
    public function getResourceNode()
    {
        if ($this->resourceNode) {
            return $this->resourceNode;
        } elseif ($this->resource) {
            return $this->resource->getResourceNode();
        } else {
            return null;
        }
    }
}
