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
 * Event dispatched by the resource controller when a resource is closed.
 */
class CloseResourceEvent extends Event
{
    /** @var AbstractResource */
    private $resource;

    /** @var bool */
    private $embedded;

    public function __construct(
        AbstractResource $resource,
        ?bool $embedded = false
    ) {
        $this->resource = $resource;
        $this->embedded = $embedded;
    }

    /**
     * Gets the closed resource Entity.
     */
    public function getResource(): ?AbstractResource
    {
        return $this->resource;
    }

    /**
     * Gets the closed resource ResourceNode entity.
     */
    public function getResourceNode(): ResourceNode
    {
        return $this->resource->getResourceNode();
    }

    public function isEmbedded(): bool
    {
        return $this->embedded;
    }
}
