<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Entity\Log\Connection;

use Claroline\CoreBundle\Entity\Resource\ResourceNode;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="Claroline\CoreBundle\Repository\Log\Connection\LogConnectResourceRepository")
 * @ORM\Table(name="claro_log_connect_resource")
 */
class LogConnectResource extends AbstractLogConnect
{
    /**
     * @ORM\ManyToOne(targetEntity="Claroline\CoreBundle\Entity\Resource\ResourceNode")
     * @ORM\JoinColumn(name="resource_id", onDelete="SET NULL", nullable=true)
     */
    protected $resource;

    /**
     * @ORM\Column(name="resource_name")
     */
    protected $resourceName;

    /**
     * @ORM\Column(name="resource_type")
     */
    protected $resourceType;

    /**
     * @ORM\Column(name="embedded", type="boolean")
     */
    protected $embedded = false;

    /**
     * @return ResourceNode
     */
    public function getResource()
    {
        return $this->resource;
    }

    /**
     * @param ResourceNode $resource
     */
    public function setResource(ResourceNode $resource = null)
    {
        $this->resource = $resource;

        if ($resource) {
            $this->setResourceName($resource->getName());
            $this->setResourceType($resource->getResourceType()->getName());
        }
    }

    /**
     * @return string
     */
    public function getResourceName()
    {
        return $this->resourceName;
    }

    /**
     * @param string $resourceName
     */
    public function setResourceName($resourceName)
    {
        $this->resourceName = $resourceName;
    }

    /**
     * @return string
     */
    public function getResourceType()
    {
        return $this->resourceType;
    }

    /**
     * @param string $resourceType
     */
    public function setResourceType($resourceType)
    {
        $this->resourceType = $resourceType;
    }

    /**
     * @return bool
     */
    public function isEmbedded()
    {
        return $this->embedded;
    }

    /**
     * @param bool $embedded
     */
    public function setEmbedded($embedded)
    {
        $this->embedded = $embedded;
    }
}
