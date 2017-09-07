<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Library\Security\Collection;

use Claroline\CoreBundle\Entity\Resource\ResourceNode;

/**
 * This is the class used by the ResourceVoter to take access decisions.
 */
class ResourceCollection
{
    /**
     * @var ResourceNode[]
     */
    private $resources;

    /**
     * @var array
     */
    private $errors;

    /**
     * @var array
     */
    private $attributes;

    /**
     * ResourceCollection constructor.
     *
     * @param array $resources
     * @param array $attributes
     */
    public function __construct(array $resources = [], $attributes = [])
    {
        $this->resources = $resources;
        $this->attributes = $attributes;
        $this->errors = [];
    }

    /**
     * @param ResourceNode $resource
     */
    public function addResource(ResourceNode $resource)
    {
        $this->resources[] = $resource;
    }

    /**
     * @param ResourceNode[] $resources
     */
    public function setResources(array $resources)
    {
        $this->resources = $resources;
    }

    /**
     * @return ResourceNode[]
     */
    public function getResources()
    {
        return $this->resources;
    }

    /**
     * Used by the ResourceVoter to set an array of errors.
     *
     * @param array $errors
     */
    public function setErrors($errors)
    {
        $this->errors = $errors;
    }

    /**
     * @return array
     */
    public function getErrors()
    {
        return $this->errors;
    }

    /**
     * @param array $attributes
     */
    public function setAttributes($attributes)
    {
        $this->attributes = $attributes;
    }

    /**
     * @return array
     */
    public function getAttributes()
    {
        return $this->attributes;
    }

    /**
     * @param string $key
     *
     * @return mixed
     */
    public function getAttribute($key)
    {
        return $this->attributes[$key];
    }

    /**
     * @param string $key
     * @param mixed  $value
     */
    public function addAttribute($key, $value)
    {
        $this->attributes[$key] = $value;
    }

    /**
     * @param string $key
     */
    public function removeAttribute($key)
    {
        unset($this->attributes[$key]);
    }

    /**
     * @return string
     */
    public function getErrorsForDisplay()
    {
        $content = '';

        foreach ($this->errors as $error) {
            $content .= "<p>{$error}</p>";
        }

        return $content;
    }
}
