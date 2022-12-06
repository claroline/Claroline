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

use Claroline\AppBundle\Entity\Identifier\Id;
use Claroline\CoreBundle\Entity\Role;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="Claroline\CoreBundle\Repository\Resource\ResourceRightsRepository")
 * @ORM\Table(
 *     name="claro_resource_rights",
 *     uniqueConstraints={
 *         @ORM\UniqueConstraint(
 *             name="resource_rights_unique_resource_role",
 *             columns={"resourceNode_id", "role_id"}
 *         )
 *     },
 *     indexes={@ORM\Index(name="mask_idx", columns={"mask"})}
 * )
 */
class ResourceRights
{
    use Id;

    /**
     * @ORM\Column(type="integer")
     */
    private $mask = 0;

    /**
     * @ORM\ManyToOne(targetEntity="Claroline\CoreBundle\Entity\Role")
     * @ORM\JoinColumn(nullable=false, onDelete="CASCADE")
     *
     * @var Role
     */
    private $role;

    /**
     * @ORM\ManyToOne(
     *     targetEntity="Claroline\CoreBundle\Entity\Resource\ResourceNode",
     *     inversedBy="rights",
     *     cascade={"persist"}
     * )
     * @ORM\JoinColumn(nullable=false, onDelete="CASCADE")
     *
     * @var ResourceNode
     */
    private $resourceNode;

    /**
     * @ORM\ManyToMany(
     *     targetEntity="Claroline\CoreBundle\Entity\Resource\ResourceType",
     *     inversedBy="rights"
     * )
     * @ORM\JoinTable(
     *     name="claro_list_type_creation",
     *     joinColumns={@ORM\JoinColumn(name="resource_rights_id", onDelete="CASCADE")},
     *     inverseJoinColumns={@ORM\JoinColumn(name="resource_type_id", onDelete="CASCADE")})
     * )
     *
     * @var ArrayCollection|ResourceType[]
     */
    private $resourceTypes;

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
