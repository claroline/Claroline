<?php

namespace Claroline\CoreBundle\Entity\Resource;

use \Exception;
use Doctrine\ORM\Mapping as ORM;
use Claroline\CoreBundle\Entity\Role;
use Claroline\CoreBundle\Entity\Resource\AbstractResource;
use Claroline\CoreBundle\Entity\Resource\ResourceType;
use Claroline\CoreBundle\Entity\Workspace\AbstractWorkspace;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * @ORM\Entity(repositoryClass="Claroline\CoreBundle\Repository\ResourceRightsRepository")
 * @ORM\Table(name="claro_resource_rights")
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
     * @ORM\ManyToOne(
     *     targetEntity="Claroline\CoreBundle\Entity\Role",
     *     inversedBy="resourceRights"
     * )
     */
    protected $role;

    /**
     * @ORM\ManyToOne(
     *     targetEntity="Claroline\CoreBundle\Entity\Resource\AbstractResource",
     *     inversedBy="rights",
     *     cascade={"persist"}
     * )
     */
    protected $resource;

    /**
     * @ORM\Column(type="boolean", name="can_delete")
     */
    protected $canDelete;

    /**
     * @ORM\Column(type="boolean", name="can_open")
     */
    protected $canOpen;

    /**
     * @ORM\Column(type="boolean", name="can_edit")
     */
    protected $canEdit;

    /**
     * @ORM\Column(type="boolean", name="can_copy")
     */
    protected $canCopy;

    /**
     * @ORM\Column(type="boolean", name="can_export")
     */
    protected $canExport;

    /**
     * @ORM\ManyToMany(
     *     targetEntity="Claroline\CoreBundle\Entity\Resource\ResourceType",
     *     inversedBy="rights"
     * )
     * @ORM\JoinTable(
     *     name="claro_list_type_creation",
     *     joinColumns={
     *         @ORM\JoinColumn(name="right_id", referencedColumnName="id")
     *     },
     *     inverseJoinColumns={
     *         @ORM\JoinColumn(name="resource_type_id", referencedColumnName="id")
     *     }
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

    public function getResource()
    {
        return $this->resource;
    }

    public function setResource(AbstractResource $resource)
    {
        $this->resource = $resource;
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

    //required by the form builder
    public function getCanOpen()
    {
        return $this->canOpen;
    }

    //required by the form builder
    public function getCanDelete()
    {
        return $this->canDelete;
    }

    //required by the form builder
    public function getCanExport()
    {
        return $this->canExport;
    }

    //required by the form builder
    public function getCanEdit()
    {
        return $this->canEdit;
    }

    //required by the form builder
    public function getCanCopy()
    {
        return $this->canCopy;
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