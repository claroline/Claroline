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
 * Event dispatched when a resource is embedded inside a rich text.
 */
class EmbedResourceEvent extends Event
{
    /** @var AbstractResource */
    private $resource;

    /** @var string */
    private $data;

    /** @var bool */
    private $populated = false;

    public function __construct(
        AbstractResource $resource
    ) {
        $this->resource = $resource;
    }

    /**
     * Gets the embedded resource Entity.
     */
    public function getResource(): AbstractResource
    {
        return $this->resource;
    }

    /**
     * Gets the embedded resource ResourceNode entity.
     */
    public function getResourceNode(): ResourceNode
    {
        return $this->resource->getResourceNode();
    }

    public function setData(string $data): void
    {
        $this->data = $data;
        $this->populated = true;
    }

    public function getData(): ?string
    {
        return $this->data;
    }

    public function isPopulated(): bool
    {
        return $this->populated;
    }
}
