<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\KernelBundle\Bundle;

class ConfigurationBuilder
{
    const RESOURCE_OBJECT = 'resource';
    const RESOURCE_TYPE = 'type';
    const ROUTING_PREFIX = 'prefix';

    private $containerResources = [];
    private $routingResources = [];

    public function addContainerResource($resource, $type = null)
    {
        $this->containerResources[] = [
            self::RESOURCE_OBJECT => $resource,
            self::RESOURCE_TYPE => $type,
        ];

        return $this;
    }

    public function getContainerResources()
    {
        return $this->containerResources;
    }

    public function addRoutingResource($resource, $type = null, $prefix = null)
    {
        $this->routingResources[] = [
            self::RESOURCE_OBJECT => $resource,
            self::RESOURCE_TYPE => $type,
            self::ROUTING_PREFIX => $prefix,
        ];

        return $this;
    }

    public function getRoutingResources()
    {
        return $this->routingResources;
    }
}
