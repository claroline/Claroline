<?php

namespace Claroline\CoreBundle\Entity\Workspace;

use Doctrine\ORM\Mapping as ORM;
use Claroline\CoreBundle\Entity\Role;
use Claroline\CoreBundle\Entity\Resource\AbstractResource;

/**
 * @ORM\Entity
 * @ORM\Table(name="claro_resource_rights")
 */
class ResourceRightsWorkspace
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\ManyToOne(targetEntity="Claroline\CoreBundle\Entity\Role", inversedBy="resourcesRightsWorkspaces")
     */
    private $role;

    /**
     * @ORM\ManyToOne(targetEntity="Claroline\CoreBundle\Entity\Resource\AbstractResource")
     */
    private $resource;

    /**
     * @ORM\Column(type="boolean", name="can_see")
     */
    protected $canSee;

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
     * @ORM\Column(type="boolean", name="can_share")
     */
    protected $canShare;

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

    public function getCanSee()
    {
        return $this->canSee;
    }

    public function setCanSee($canSee)
    {
        $this->canSee = $canSee;
    }

    public function getCanDelete()
    {
        return $this->canDelete;
    }

    public function setCanDelete($canDelete)
    {
        $this->canDelete = $canDelete;
    }

    public function getCanOpen()
    {
        return $this->canOpen;
    }

    public function setCanOpen($canOpen)
    {
        $this->canOpen = $canOpen;
    }

    public function getCanEdit()
    {
        return $this->canEdit;
    }

    public function setCanEdit($canEdit)
    {
        $this->canEdit = $canEdit;
    }

    public function getCanCopy()
    {
        return $this->canCopy;
    }

    public function setCanCopy($canCopy)
    {
        $this->canCopy = $canCopy;
    }

    public function getCanShare()
    {
        return $this->canShare;
    }

    public function setCanShare($canShare)
    {
        $this->canShare = $canShare;
    }
}