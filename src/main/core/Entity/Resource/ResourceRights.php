<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Entity\Resource;

use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Claroline\CoreBundle\Repository\Resource\ResourceRightsRepository;
use Claroline\AppBundle\Entity\Identifier\Id;
use Claroline\CoreBundle\Entity\Role;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Table(name: 'claro_resource_rights')]
#[ORM\Index(name: 'mask_idx', columns: ['mask'])]
#[ORM\UniqueConstraint(name: 'resource_rights_unique_resource_role', columns: ['resourceNode_id', 'role_id'])]
#[ORM\Entity(repositoryClass: ResourceRightsRepository::class)]
class ResourceRights
{
    use Id;

    #[ORM\Column(type: Types::INTEGER)]
    private $mask = 0;

    /**
     *
     * @var Role
     */
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    #[ORM\ManyToOne(targetEntity: Role::class)]
    private ?Role $role = null;

    /**
     *
     * @var ResourceNode
     */
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    #[ORM\ManyToOne(targetEntity: ResourceNode::class, inversedBy: 'rights', cascade: ['persist'])]
    private ?ResourceNode $resourceNode = null;

    /**
     *
     * @var Collection<int, ResourceType>
     */
    #[ORM\JoinTable(name: 'claro_list_type_creation')]
    #[ORM\JoinColumn(name: 'resource_rights_id', onDelete: 'CASCADE')]
    #[ORM\InverseJoinColumn(name: 'resource_type_id', onDelete: 'CASCADE')]
    #[ORM\ManyToMany(targetEntity: ResourceType::class, inversedBy: 'rights')]
    private Collection $resourceTypes;

    public function __construct()
    {
        $this->resourceTypes = new ArrayCollection();
    }

    public function getRole(): ?Role
    {
        return $this->role;
    }

    public function setRole(Role $role): void
    {
        $this->role = $role;
    }

    public function getResourceNode(): ?ResourceNode
    {
        return $this->resourceNode;
    }

    public function setResourceNode(?ResourceNode $resourceNode = null): void
    {
        $this->resourceNode = $resourceNode;
    }

    public function getMask(): ?int
    {
        return $this->mask;
    }

    public function setMask(int $mask): void
    {
        $this->mask = $mask;
    }

    public function getCreatableResourceTypes()
    {
        return $this->resourceTypes;
    }

    public function setCreatableResourceTypes(array $resourceTypes): void
    {
        $this->resourceTypes = new ArrayCollection($resourceTypes);
    }

    public function addCreatableResourceType(ResourceType $resourceType): void
    {
        if (!$this->resourceTypes->contains($resourceType)) {
            $this->resourceTypes->add($resourceType);
        }
    }

    public function removeCreatableResourceType(ResourceType $resourceType): void
    {
        if ($this->resourceTypes->contains($resourceType)) {
            $this->resourceTypes->removeElement($resourceType);
        }
    }
}
