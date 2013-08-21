<?php

namespace Claroline\CoreBundle\Entity\Resource;

use Doctrine\ORM\Mapping as ORM;
use Claroline\CoreBundle\Entity\Role;
use Claroline\CoreBundle\Entity\Resource\AbstractResource;
use Claroline\CoreBundle\Entity\Resource\ResourceType;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * @ORM\Entity(repositoryClass="Claroline\CoreBundle\Repository\ResourceRightsRepository")
 * @ORM\Table(
 *     name="claro_resource_rights",
 *     uniqueConstraints={
 *         @ORM\UniqueConstraint(
 *             name="resource_rights_unique_resource_role",
 *             columns={"resourceNode_id", "role_id"}
 *         )
 *     }
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
     * @ORM\Column(name="can_delete", type="boolean")
     */
    protected $canDelete = false;

    /**
     * @ORM\Column(name="can_open", type="boolean")
     */
    protected $canOpen = false;

    /**
     * @ORM\Column(name="can_edit", type="boolean")
     */
    protected $canEdit = false;

    /**
     * @ORM\Column(name="can_copy", type="boolean")
     */
    protected $canCopy = false;

    /**
     * @ORM\Column(name="can_export", type="boolean")
     */
    protected $canExport = false;

    /**
     * @ORM\ManyToOne(
     *     targetEntity="Claroline\CoreBundle\Entity\Role",
     *     inversedBy="resourceRights"
     * )
     * @ORM\JoinColumn(nullable=false, onDelete="CASCADE")
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

    public function canDelete()
    {
        return $this->canDelete;
    }

    public function setCanDelete($canDelete)
    {
        $this->canDelete = $canDelete;
    }

    public function canOpen()
    {
        return $this->canOpen;
    }

    public function setCanOpen($canOpen)
    {
        $this->canOpen = $canOpen;
    }

    public function canEdit()
    {
        return $this->canEdit;
    }

    public function setCanEdit($canEdit)
    {
        $this->canEdit = $canEdit;
    }

    public function canCopy()
    {
        return $this->canCopy;
    }

    public function setCanCopy($canCopy)
    {
        $this->canCopy = $canCopy;
    }

    public function setCanExport($canExport)
    {
        $this->canExport = $canExport;
    }

    public function canExport()
    {
        return $this->canExport;
    }

    public function setRightsFrom(ResourceRights $originalRights)
    {
        $this->setCanOpen($originalRights->canOpen());
        $this->setCanEdit($originalRights->canEdit());
        $this->setCanDelete($originalRights->canDelete());
        $this->setCanCopy($originalRights->canCopy());
        $this->setCanExport($originalRights->canExport());
    }

    public function getPermissions()
    {
        return array(
            'canOpen' => $this->canOpen,
            'canEdit' => $this->canEdit,
            'canDelete' => $this->canDelete,
            'canCopy' => $this->canCopy,
            'canExport' => $this->canExport,
        );
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
