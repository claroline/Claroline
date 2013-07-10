<?php

namespace Claroline\CoreBundle\Event\Event;

use Symfony\Component\EventDispatcher\Event;
use Claroline\CoreBundle\Entity\Resource\AbstractResource;

/**
 * Event dispatched by the resource controller when a resource creation is asked.
 */
class CreateResourceEvent extends Event implements DataConveyorEventInterface
{
    private $resource;
    private $formContent;
    private $resourceType;
    private $resources;
    private $isPopulated = false;

    public function __construct($resourceType = null)
    {
        $this->resourceType = $resourceType;
        $this->resources = array();
    }

    /**
     * Sets the form content with validations errors (failure scenario)
     *
     * @param string $formContent
     */
    public function setErrorFormContent($formContent)
    {
        $this->isPopulated = true;
        $this->formContent = $formContent;
    }

    /**
     * Returns the form content with validation errors
     * @return string
     */
    public function getErrorFormContent()
    {
        return $this->formContent;
    }

    public function setResourceType($resourceType)
    {
        $this->resourceType = $resourceType;
    }

    /**
     * Return the resource type (used by the file manager)
     * @return string
     */
    public function getResourceType()
    {
        return $this->resourceType;
    }

    public function getResources()
    {
        return $this->resources;
    }

    public function setResources(array $resources)
    {
        $this->isPopulated = true;
        $this->resources = $resources;
    }

    public function isPopulated()
    {
        return $this->isPopulated;
    }
}