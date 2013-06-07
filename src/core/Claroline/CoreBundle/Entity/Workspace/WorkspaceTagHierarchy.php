<?php

namespace Claroline\CoreBundle\Entity\Workspace;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="Claroline\CoreBundle\Repository\WorkspaceTagHierarchyRepository")
 * @ORM\Table(name="claro_workspace_tag_hierarchy")
 */
class WorkspaceTagHierarchy
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\ManyToOne(
     *     targetEntity="Claroline\CoreBundle\Entity\User"
     * )
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id", nullable=true)
     */
    protected $user;

    /**
     * @ORM\ManyToOne(
     *     targetEntity="Claroline\CoreBundle\Entity\Workspace\WorkspaceTag"
     * )
     * @ORM\JoinColumn(name="tag_id", referencedColumnName="id", nullable=false)
     */
    protected $tag;

    /**
     * @ORM\ManyToOne(
     *     targetEntity="Claroline\CoreBundle\Entity\Workspace\WorkspaceTag"
     * )
     * @ORM\JoinColumn(name="parent_id", referencedColumnName="id", nullable=false)
     */
    protected $parent;

    /**
     * @ORM\Column(type="integer", nullable=false)
     */
    protected $level;

    public function getId()
    {
        return $this->id;
    }

    public function setId($id)
    {
        $this->id = $id;
    }

    public function getUser()
    {
        return $this->user;
    }

    public function setUser($user)
    {
        $this->user = $user;
    }

    public function getTag()
    {
        return $this->tag;
    }

    public function setTag($tag)
    {
        $this->tag = $tag;
    }

    public function getParent()
    {
        return $this->parent;
    }

    public function setParent($parent)
    {
        $this->parent = $parent;
    }

    public function getLevel()
    {
        return $this->level;
    }

    public function setLevel($level)
    {
        $this->level = $level;
    }
}