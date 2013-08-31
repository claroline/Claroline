<?php

namespace Claroline\KernelBundle\Bundle;

class ConfigurationBuilder
{
    const RESOURCE_OBJECT = 'resource';
    const RESOURCE_TYPE = 'type';

    private $containerResources = array();
    private $routingResources = array();

    public function addContainerResource($resource, $type = null)
    {
        return $this->addResource($this->containerResources, $resource, $type);
    }

    public function getContainerResources()
    {
        return $this->containerResources;
    }

    public function addRoutingResource($resource, $type = null)
    {
        return $this->addResource($this->routingResources, $resource, $type);
    }

    public function getRoutingResources()
    {
        return $this->routingResources;
    }

    private function addResource(array &$collection, $resource, $type = null)
    {
        $collection[] = array(
            self::RESOURCE_OBJECT => $resource,
            self::RESOURCE_TYPE => $type
        );

        return $this;
    }
}
