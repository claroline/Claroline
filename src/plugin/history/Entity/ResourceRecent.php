<?php

namespace Claroline\HistoryBundle\Entity;

use Claroline\CoreBundle\Entity\Resource\ResourceNode;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="Claroline\HistoryBundle\Repository\ResourceRecentRepository")
 * @ORM\Table(name="claro_resource_recent", indexes={
 *     @ORM\Index(name="user_idx", columns={"user_id"})
 * })
 */
class ResourceRecent extends AbstractRecent
{
    /**
     * @ORM\ManyToOne(
     *     targetEntity="Claroline\CoreBundle\Entity\Resource\ResourceNode"
     * )
     * @ORM\JoinColumn(nullable=false, onDelete="CASCADE")
     *
     * @var ResourceNode
     */
    private $resource;

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
    public function setResource(ResourceNode $resource)
    {
        $this->resource = $resource;
    }
}
