<?php

namespace Claroline\CoreBundle\Entity\Widget;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * @ORM\Entity(repositoryClass="Claroline\CoreBundle\Repository\DisplayConfigRepository")
 * @ORM\Table(name="claro_widget_dispay")
 * @Gedmo\Tree(type="nested")
 */
class DisplayConfig
{
    const ADMIN_LEVEL = 0;
    const WORKSPACE_LEVEL = 1;
    const USER_LEVEL = 2;

    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @Gedmo\TreeLeft
     * @ORM\Column(name="lft", type="integer")
     */
    protected $lft;

    /**
     * @Gedmo\TreeLevel
     * @ORM\Column(name="lvl", type="integer")
     */
    protected $lvl;

    /**
     * @Gedmo\TreeRight
     * @ORM\Column(name="rgt", type="integer")
     */
    protected $rgt;

    /**
     * @Gedmo\TreeRoot
     * @ORM\Column(name="root", type="integer", nullable=true)
     */
    protected $root;

    /**
     * @Gedmo\TreeParent
     * @ORM\ManyToOne(
     *      targetEntity="Claroline\CoreBundle\Entity\Widget\DisplayConfig",
     *      inversedBy="children"
     * )
     * @ORM\JoinColumn(
     *      name="parent_id",
     *      referencedColumnName="id",
     *      onDelete="SET NULL"
     * )
     */
    protected $parent;

    /**
     * @ORM\OneToMany(
     *      targetEntity="Claroline\CoreBundle\Entity\Widget\DisplayConfig",
     *      mappedBy="parent"
     * )
     * @ORM\OrderBy({"lft" = "ASC"})
     */
    protected $children;

    /**
     * @ORM\ManyToOne(targetEntity="Claroline\CoreBundle\Entity\Workspace\AbstractWorkspace")
     * @ORM\JoinColumn(name="workspace_id", referencedColumnName="id")
     */
    protected $workspace;

    /**
     * @ORM\ManyToOne(targetEntity="Claroline\CoreBundle\Entity\User")
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id")
     */
    protected $user;

    /**
     * @ORM\ManyToOne(targetEntity="Claroline\CoreBundle\Entity\Widget\Widget")
     * @ORM\JoinColumn(name="widget_id", referencedColumnName="id")
     */
    protected $widget;

    /**
     * @ORM\Column(type="boolean", name="is_locked")
     */
    protected $isLocked;

    /**
     * @ORM\Column(type="boolean", name="is_visible")
     */
    protected $isVisible;

    /**
     * @ORM\Column(type="boolean", name="is_desktop")
     */
    protected $isDesktop;

    public function getId()
    {
        return $this->id;
    }

    public function setWidget($widget)
    {
        $this->widget = $widget;
    }

    public function getWidget()
    {
        return $this->widget;
    }

    public function setLock($bool)
    {
        $this->isLocked = $bool;
    }

    public function isLocked()
    {
        return $this->isLocked;
    }

    public function invertLock()
    {
        ($this->isLocked == true) ? $this->isLocked = false: $this->isLocked = true;
    }

    public function isVisible()
    {
        return $this->isVisible;
    }

    public function setVisible($bool)
    {
        $this->isVisible = $bool;
    }

    public function invertVisible()
    {
        ($this->isVisible == true) ? $this->isVisible = false: $this->isVisible = true;
    }

    public function getParent()
    {
        return $this->parent;
    }

    public function setParent($parent)
    {
        $this->parent = $parent;
    }

    public function getLvl()
    {
        return $this->lvl;
    }

    public function setLvl($lvl)
    {
        $this->lvl = $lvl;
    }

    public function getWorkspace()
    {
        return $this->workspace;
    }

    public function setWorkspace($workspace)
    {
        $this->workspace = $workspace;
    }

    public function setDesktop($bool)
    {
        $this->isDesktop = $bool;
    }

    public function isDesktop()
    {
        return $this->isDesktop;
    }

    public function setUser($user)
    {
        $this->user = $user;
    }

    public function getUser()
    {
        return $this->user;
    }
}
