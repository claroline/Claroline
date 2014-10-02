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

use Claroline\CoreBundle\Entity\Resource\ResourceNode;
use Symfony\Component\EventDispatcher\Event;
use Claroline\CoreBundle\Event\DataConveyorEventInterface;
use Claroline\CoreBundle\Entity\Resource\AbstractResource;

/**
 * Event dispatched by the resource controller when a resource copy is asked.
 */
class CopyResourceEvent extends Event implements DataConveyorEventInterface
{
    private $resource;
    private $parent;
    private $copy;
    private $isPopulated = false;

    /**
     * Constructor.
     *
     * @param \Claroline\CoreBundle\Entity\Resource\AbstractResource $resource
     * @param \Claroline\CoreBundle\Entity\Resource\ResourceNode $parent
     */
    public function __construct(AbstractResource $resource, ResourceNode $parent)
    {
        $this->resource = $resource;
        $this->parent   = $parent;
    }

    /**
     * Returns the new parent of the resource
     * @return \Claroline\CoreBundle\Entity\Resource\ResourceNode
     */
    public function getParent()
    {
        return $this->parent;
    }

    /**
     * Returns the resource to be copied.
     *
     * @return \Claroline\CoreBundle\Entity\Resource\AbstractResource
     */
    public function getResource()
    {
        return $this->resource;
    }

    /**
     * Sets the copy of the original resource.
     *
     * @param \Claroline\CoreBundle\Entity\Resource\AbstractResource $copy
     */
    public function setCopy(AbstractResource $copy)
    {
        $this->isPopulated = true;
        $this->copy = $copy;
    }

    /**
     * Returns the copy of the original resource.
     *
     * @return \Claroline\CoreBundle\Entity\Resource\AbstractResource
     */
    public function getCopy()
    {
        return $this->copy;
    }

    public function isPopulated()
    {
        return $this->isPopulated;
    }
}
