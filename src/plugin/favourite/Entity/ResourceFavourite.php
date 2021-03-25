<?php

namespace HeVinci\FavouriteBundle\Entity;

use Claroline\CoreBundle\Entity\Resource\ResourceNode;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="claro_resource_favourite", uniqueConstraints={
 *     @ORM\uniqueConstraint(columns={"user_id", "resource_node_id"})
 * })
 */
class ResourceFavourite extends AbstractFavourite
{
    /**
     * @ORM\ManyToOne(targetEntity="Claroline\CoreBundle\Entity\Resource\ResourceNode")
     * @ORM\JoinColumn(name="resource_node_id", onDelete="CASCADE")
     *
     * @var ResourceNode
     */
    private $resource;

    public function setResource(ResourceNode $resourceNode)
    {
        $this->resource = $resourceNode;
    }

    /**
     * @return ResourceNode
     */
    public function getResource()
    {
        return $this->resource;
    }
}
