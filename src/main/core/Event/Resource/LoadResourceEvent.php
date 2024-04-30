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
 * Event dispatched by the resource controller when a resource is loaded from the api.
 */
class LoadResourceEvent extends Event
{
    private array $data = [];

    public function __construct(
        private readonly AbstractResource $resource,
        private readonly ?bool $embedded = false
    ) {
    }

    /**
     * Gets the loaded resource Entity.
     */
    public function getResource(): AbstractResource
    {
        return $this->resource;
    }

    /**
     * Gets the loaded resource ResourceNode entity.
     */
    public function getResourceNode(): ResourceNode
    {
        return $this->resource->getResourceNode();
    }

    public function isEmbedded(): bool
    {
        return $this->embedded;
    }

    /**
     * Sets data to return in the api.
     * NB. It MUST contain serialized structures.
     */
    public function setData(array $data): void
    {
        $this->data = $data;
    }

    public function getData(): ?array
    {
        return $this->data;
    }
}
