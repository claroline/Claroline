<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Security\Collection;

use Claroline\CoreBundle\Entity\Resource\ResourceNode;

/**
 * This is the class used by the ResourceVoter to take access decisions.
 */
class ResourceCollection
{
    /**
     * @var ResourceNode[]
     */
    private array $resources;

    private array $errors = [];

    private array $attributes;

    public function __construct(?array $resources = [], ?array $attributes = [])
    {
        $this->resources = $resources;
        $this->attributes = $attributes;
    }

    public function addResource(ResourceNode $resource): void
    {
        $this->resources[] = $resource;
    }

    /**
     * @param ResourceNode[] $resources
     */
    public function setResources(array $resources): void
    {
        $this->resources = $resources;
    }

    /**
     * @return ResourceNode[]
     */
    public function getResources(): array
    {
        return $this->resources;
    }

    /**
     * Used by the ResourceVoter to set an array of errors.
     */
    public function setErrors(array $errors): void
    {
        $this->errors = $errors;
    }

    public function getErrors(): array
    {
        return $this->errors;
    }

    public function setAttributes(array $attributes): void
    {
        $this->attributes = $attributes;
    }

    public function getAttributes(): array
    {
        return $this->attributes;
    }

    public function getAttribute(string $key): mixed
    {
        return $this->attributes[$key] ?? null;
    }

    public function addAttribute(string $key, mixed $value): void
    {
        $this->attributes[$key] = $value;
    }

    public function removeAttribute(string $key): void
    {
        unset($this->attributes[$key]);
    }

    public function getErrorsForDisplay(): string
    {
        $content = '';

        foreach ($this->errors as $error) {
            $content .= "<p>{$error}</p>";
        }

        return $content;
    }
}
