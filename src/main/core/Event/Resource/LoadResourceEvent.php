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
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * Event dispatched by the resource controller when a resource is loaded from the api.
 */
class LoadResourceEvent extends Event implements MandatoryEventInterface, DataConveyorEventInterface
{
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

    public function __construct(
        AbstractResource $resource,
        ?User $user = null,
        ?bool $embedded = false
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
        $this->populated = true;
    }

    public function getData(): ?array
    {
        return $this->data;
    }

    public function isPopulated(): bool
    {
        return $this->populated;
    }

    public function getMessage(TranslatorInterface $translator): string
    {
        return $translator->trans('resourceOpen', ['userName' => $this->user->getUsername(), 'resourceName' => $this->getResourceNode()->getName()], 'resource');
    }
}
