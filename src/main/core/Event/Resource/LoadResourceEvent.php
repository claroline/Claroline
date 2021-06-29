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

use Claroline\AppBundle\Event\DataConveyorEventInterface;
use Claroline\AppBundle\Event\MandatoryEventInterface;
use Claroline\CoreBundle\Entity\Resource\AbstractResource;
use Claroline\CoreBundle\Entity\Resource\ResourceNode;
use Claroline\CoreBundle\Entity\User;
use Symfony\Contracts\EventDispatcher\Event;

/**
 * Event dispatched by the resource controller when a resource is loaded from the api.
 */
class LoadResourceEvent extends Event implements MandatoryEventInterface, DataConveyorEventInterface
{
    public const EVENT_NAME = 'resource_load';

    /** @var AbstractResource */
    private $resource;

    /** @var bool */
    private $embedded;

    /** @var array */
    private $data = [];

    /** @var bool */
    private $populated = false;

    /** @var User */
    private $user;

    /**
     * LoadResourceEvent constructor.
     *
     * @param bool $embedded
     */
    public function __construct(
        AbstractResource $resource,
        ?User $user = null,
        $embedded = false
    ) {
        $this->resource = $resource;
        $this->user = $user;
        $this->embedded = $embedded;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    /**
     * Gets the loaded resource Entity.
     *
     * @return AbstractResource
     */
    public function getResource()
    {
        return $this->resource;
    }

    /**
     * Gets the loaded resource ResourceNode entity.
     *
     * @return ResourceNode
     */
    public function getResourceNode()
    {
        return $this->resource->getResourceNode();
    }

    public function isEmbedded()
    {
        return $this->embedded;
    }

    /**
     * Sets data to return in the api.
     * NB. It MUST contain serialized structures.
     */
    public function setData(array $data)
    {
        $this->data = $data;
        $this->populated = true;
    }

    public function getData()
    {
        return $this->data;
    }

    public function isPopulated()
    {
        return $this->populated;
    }
}
