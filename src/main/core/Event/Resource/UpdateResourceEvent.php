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
 * Event dispatched when a resource is updated.
 */
class UpdateResourceEvent extends Event
{
    private array $response = [];

    public function __construct(
        private readonly AbstractResource $resource,
        private readonly ?array $data = []
    ) {
    }

    /**
     * Gets the resource ResourceNode entity.
     */
    public function getResourceNode(): ResourceNode
    {
        return $this->resource->getResourceNode();
    }

    public function getResource(): AbstractResource
    {
        return $this->resource;
    }

    public function getData(): array
    {
        return $this->data;
    }

    /**
     * Sets response data to return in the api.
     * NB. It MUST contain serialized structures.
     */
    public function addResponse(array $responseData): void
    {
        $this->response = array_merge($responseData, $this->response);
    }

    public function getResponse(): array
    {
        return $this->response;
    }
}