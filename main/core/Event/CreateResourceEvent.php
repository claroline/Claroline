<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Event;

use Symfony\Component\EventDispatcher\Event;
use Claroline\CoreBundle\Entity\Resource\ResourceNode;

/**
 * Event dispatched by the resource controller when a resource creation is asked.
 */
class CreateResourceEvent extends Event implements DataConveyorEventInterface
{
    private $parent;
    private $formContent;
    private $resourceType;
    private $resources;
    private $isPopulated = false;
    private $process = true;
    private $published = true;
    private $encoding;

    public function __construct($parent = null, $resourceType = null, $encoding = 'none')
    {
        $this->parent = $parent;
        $this->resourceType = $resourceType;
        $this->resources = array();
        $this->encoding = $encoding;
    }

    /**
     * Sets the form content with validations errors (failure scenario).
     *
     * @param string $formContent
     */
    public function setErrorFormContent($formContent)
    {
        $this->isPopulated = true;
        $this->formContent = $formContent;
    }

    /**
     * Returns the form content with validation errors.
     *
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
     * Return the resource type (used by the file manager).
     *
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

    /**
     * Required for the unzipping stuff.
     */
    public function setProcess($boolean)
    {
        $this->process = $boolean;
    }

    public function getProcess()
    {
        return $this->process;
    }

    public function setParent(ResourceNode $parent)
    {
        $this->parent = $parent;
    }

    public function getParent()
    {
        return $this->parent;
    }

    public function setPublished($published)
    {
        $this->published = $published;
    }

    public function isPublished()
    {
        return $this->published;
    }

    public function getEncoding()
    {
        return $this->encoding;
    }
}
