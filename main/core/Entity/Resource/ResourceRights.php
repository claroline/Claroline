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

use Claroline\CoreBundle\Entity\Role;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\Index;

/**
 * @ORM\Entity(repositoryClass="Claroline\CoreBundle\Repository\ResourceRightsRepository")
 * @ORM\Table(
 *     name="claro_resource_rights",
 *     uniqueConstraints={
 *         @ORM\UniqueConstraint(
 *             name="resource_rights_unique_resource_role",
 *             columns={"resourceNode_id", "role_id"}
 *         )
 *     },
 *     indexes={@Index(name="mask_idx", columns={"mask"})}
 * )
 */
class ResourceRights
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\Column(type="integer")
     */
    protected $mask = 0;

    /**
     * @ORM\ManyToOne(
     *     targetEntity="Claroline\CoreBundle\Entity\Role",
     *     inversedBy="resourceRights"
     * )
     * @ORM\JoinColumn(nullable=false, onDelete="CASCADE")
     *
     * @var Role
     */
    protected $role;

    /**
     * @ORM\ManyToOne(
     *     targetEntity="Claroline\CoreBundle\Entity\Resource\ResourceNode",
     *     inversedBy="rights",
     *     cascade={"persist"}
     * )
     * @ORM\JoinColumn(nullable=false, onDelete="CASCADE")
     */
    protected $resourceNode;

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
     */
    protected $resourceTypes;

    public function __construct()
    {
        $this->resourceTypes = new ArrayCollection();
    }

    public function getId()
    {
        return $this->id;
    }

    /**
     * Get Role.
     *
     * @return Role
     */
    public function getRole()
    {
        return $this->role;
    }

    public function setRole(Role $role)
    {
        $this->role = $role;
    }

    public function getResourceNode()
    {
        return $this->resourceNode;
    }

    public function setResourceNode(ResourceNode $resourceNode)
    {
        $this->resourceNode = $resourceNode;
    }

    public function getMask()
    {
        return $this->mask;
    }

    public function setMask($mask)
    {
        $this->mask = $mask;
    }

    public function getCreatableResourceTypes()
    {
        return $this->resourceTypes;
    }

    public function setCreatableResourceTypes(array $resourceTypes)
    {
        $this->resourceTypes = new ArrayCollection($resourceTypes);
    }

    public function addCreatableResourceType(ResourceType $resourceType)
    {
        $this->resourceTypes->add($resourceType);
    }

    public function removeCreatableResourceType(ResourceType $resourceType)
    {
        $this->resourceTypes->removeElement($resourceType);
    }
}
