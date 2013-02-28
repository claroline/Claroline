<?php

namespace Claroline\CoreBundle\Library\Event;

use Symfony\Component\EventDispatcher\Event;
use Claroline\CoreBundle\Entity\Resource\AbstractResource;

/**
 * Event dispatched by the resource controller when a resource creation is asked.
 */
class CreateResourceEvent extends Event
{
    private $resource;
    private $formContent;
    private $resourceType;

    public function __construct($resourceType = null)
    {
        $this->resourceType = $resourceType;
    }

    /**
     * Sets the newly created resource (success scenario).
     *
     * @param AbstractResource $resource
     */
    public function setResource(AbstractResource $resource)
    {
        $this->resource = $resource;
    }

    /**
     * Returns the newly created resource.
     *
     * @return AbstractResource
     */
    public function getResource()
    {
        return $this->resource;
    }

    /**
     * Sets the form content with validations errors (failure scenario)
     *
     * @param string $formContent
     */
    public function setErrorFormContent($formContent)
    {
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
}