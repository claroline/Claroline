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
    private $resources;
    private $errors;
    private $attributes;

    public function __construct(array $resources = array(), $attributes = array())
    {
        $this->resources = $resources;
        $this->attributes = $attributes;
        $this->errors = array();
    }

    public function addResource(ResourceNode $resource)
    {
        $this->resources[] = $resource;
    }

    public function setResources($resources)
    {
        $this->resources = $resources;
    }

    public function getResources()
    {
        return $this->resources;
    }

    /**
     * Used by the ResourceVoter to set an array of errors.
     *
     * @param string $errors
     *
     * @return array
     */
    public function setErrors($errors)
    {
        $this->errors = $errors;
    }

    public function getErrors()
    {
        return $this->errors;
    }

    public function setAttributes($attributes)
    {
        $this->attributes = $attributes;
    }

    public function getAttributes()
    {
        return $this->attributes;
    }

    public function getAttribute($key)
    {
        return $this->attributes[$key];
    }

    public function addAttribute($key, $value)
    {
        $this->attributes[$key] = $value;
    }

    public function removeAttribute($key)
    {
        unset($this->attributes[$key]);
    }

    public function getErrorsForDisplay()
    {
        $content = '';

        foreach ($this->errors as $error) {
            $content .= "<p>{$error}</p>";
        }

        return $content;
    }
}
