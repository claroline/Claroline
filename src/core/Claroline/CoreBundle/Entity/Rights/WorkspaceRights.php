<?php

namespace Claroline\CoreBundle\Entity\Rights;

use Doctrine\ORM\Mapping as ORM;
use Claroline\CoreBundle\Entity\Role;
use Claroline\CoreBundle\Entity\Workspace\AbstractWorkspace;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * @ORM\Entity(repositoryClass="Claroline\CoreBundle\Repository\WorkspaceRightsRepository")
 * @ORM\Table(name="claro_workspace_rights")
 */
class WorkspaceRights
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\ManyToOne(targetEntity="Claroline\CoreBundle\Entity\Role", inversedBy="workspaceRights")
     */
    private $role;

    /**
     * @ORM\ManyToOne(targetEntity="Claroline\CoreBundle\Entity\Workspace\AbstractWorkspace", inversedBy="rights", cascade={"persist"})
     */
    private $workspace;

    /**
     * @ORM\Column(type="boolean", name="can_view")
     */
    protected $canView;

    /**
     * @ORM\Column(type="boolean", name="can_edit")
     */
    protected $canEdit;

    /**
     * @ORM\Column(type="boolean", name="can_delete")
     */
    protected $canDelete;

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

    public function getWorkspace()
    {
        return $this->workspace;
    }

    public function setWorkspace(AbstractWorkspace $workspace)
    {
        $this->workspace = $workspace;
    }

    public function canView()
    {
        return $this->canView;
    }

    public function setCanView($boolean)
    {
        $this->canView = $boolean;
    }

    public function canDelete()
    {
        return $this->canDelete;
    }

    public function setCanDelete($boolean)
    {
        $this->canDelete = $boolean;
    }

    public function canEdit()
    {
        return $this->canEdit;
    }

    public function setCanEdit($boolean)
    {
        $this->canEdit = $boolean;
    }



    /**
     * Sets every right to false
     */
    public function reset()
    {
        $this->canView = false;
    }

    /**
     * Compares the current permission with an array of permission
     *
     * @param type $array
     *
     * @return boolean
     */
    public function isEquals($rights)
    {
        foreach($this->getRights() as $key => $current){
            if($current != $rights[$key]){
                return false;
            }
        }

        return true;
    }

    /**
     * Gets an array with the current permissions
     *
     * @return array
     */
    public function getRights()
    {
        return array(
            'canView' => $this->canView,
            'canEdit' => $this->canEdit,
            'canDelete' => $this->canDelete
        );
    }

    /**
     * Sets the current permission from an array
     *
     * @param type array
     */
    public function setRights($rights)
    {
        foreach($rights as $key => $value){
            $this->$key = $value;
        }
    }

    /**
     * Returns the specified right.
     *
     * @param type $right
     * @return type
     */
    public function getRight($right)
    {
        if (property_exists($this, $right))
        {
            return $this->$right;
        } else {
            throw new \RuntimeException("Property {$right} doesn't exists in the entity WorkspaceRights");
        }
    }
}