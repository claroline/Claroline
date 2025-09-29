<?php

namespace Claroline\CoreBundle\Entity\Resource;

use Claroline\AppBundle\API\Attribute\CrudEntity;
use Claroline\AppBundle\Entity\AbstractUserView;
use Claroline\CoreBundle\Finder\ResourceViewType;
use Claroline\CoreBundle\Repository\Resource\ResourceViewRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Table('claro_resource_view')]
#[ORM\Entity(repositoryClass: ResourceViewRepository::class)]
#[CrudEntity(finderClass: ResourceViewType::class)]
class ResourceView extends AbstractUserView
{
    #[ORM\JoinColumn(name: 'resource_id', nullable: false, onDelete: 'CASCADE')]
    #[ORM\ManyToOne(targetEntity: ResourceNode::class)]
    private ?ResourceNode $resourceNode = null;

    public function getSubject(): ResourceNode
    {
        return $this->resourceNode;
    }

    /** @param ResourceNode $subject */
    public function setSubject(object $subject): void
    {
        $this->resourceNode = $subject;
    }
}
