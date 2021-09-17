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
use Symfony\Contracts\EventDispatcher\Event;

/**
 * Event dispatched by the resource controller when a resource copy is asked.
 */
class CopyResourceEvent extends Event
{
    /** @var AbstractResource */
    private $resource;

    /** @var AbstractResource */
    private $copy;

    public function __construct(AbstractResource $resource, AbstractResource $copy)
    {
        $this->resource = $resource;
        $this->copy = $copy;
    }

    /**
     * Returns the new parent of the resource.
     *
     * @deprecated this can be retrieved directly from the `copiedNode`
     */
    public function getParent(): ?ResourceNode
    {
        return $this->copy->getResourceNode()->getParent();
    }

    public function getCopy()
    {
        return $this->copy;
    }

    /**
     * Returns the resource to be copied.
     */
    public function getResource(): AbstractResource
    {
        return $this->resource;
    }

    /**
     * Sets the copy of the original resource.
     */
    public function setCopy(AbstractResource $copy)
    {
        $this->copy = $copy;
    }
}
