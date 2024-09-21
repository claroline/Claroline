<?php

namespace HeVinci\FavouriteBundle\Entity;

use Claroline\CoreBundle\Entity\Resource\ResourceNode;
use Doctrine\ORM\Mapping as ORM;


#[ORM\Table(name: 'claro_resource_favourite')]
#[ORM\UniqueConstraint(columns: ['user_id', 'resource_node_id'])]
#[ORM\Entity]
class ResourceFavourite extends AbstractFavourite
{
    /**
     *
     *
     * @var ResourceNode
     */
    #[ORM\JoinColumn(name: 'resource_node_id', onDelete: 'CASCADE')]
    #[ORM\ManyToOne(targetEntity: \Claroline\CoreBundle\Entity\Resource\ResourceNode::class)]
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
