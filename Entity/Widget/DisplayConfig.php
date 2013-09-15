<?php

namespace Claroline\CoreBundle\Entity\Widget;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="Claroline\CoreBundle\Repository\DisplayConfigRepository")
 * @ORM\Table(name="claro_widget_display")
 */
class DisplayConfig
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\ManyToOne(
     *     targetEntity="Claroline\CoreBundle\Entity\Widget\DisplayConfig",
     *     inversedBy="children"
     * )
     * @ORM\JoinColumn(onDelete="SET NULL")
     */
    protected $parent;

    /**
     * @ORM\OneToMany(
     *     targetEntity="Claroline\CoreBundle\Entity\Widget\DisplayConfig",
     *     mappedBy="parent"
     * )
     */
    protected $children;

    /**
     * @ORM\ManyToOne(targetEntity="Claroline\CoreBundle\Entity\Workspace\AbstractWorkspace")
     * @ORM\JoinColumn(onDelete="CASCADE")
     */
    protected $workspace;

    /**
     * @ORM\ManyToOne(targetEntity="Claroline\CoreBundle\Entity\User")
     * @ORM\JoinColumn(onDelete="CASCADE")
     */
    protected $user;

    /**
     * @ORM\ManyToOne(targetEntity="Claroline\CoreBundle\Entity\Widget\Widget")
     * @ORM\JoinColumn(onDelete="CASCADE", nullable=false)
     *
     * @var Widget
     */
    protected $widget;

    /**
     * @ORM\Column(name="is_locked", type="boolean")
     */
    protected $isLocked = false;

    /**
     * @ORM\Column(name="is_visible", type="boolean")
     */
    protected $isVisible = false;

    /**
     * @ORM\Column(name="is_desktop", type="boolean")
     */
    protected $isDesktop = false;

    public function getId()
    {
        return $this->id;
    }

    public function setWidget($widget)
    {
        $this->widget = $widget;
    }

    /**
     * @return Widget
     */
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
        $this->isLocked = !$this->isLocked;
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
        $this->isVisible = !$this->isVisible;
    }

    public function getParent()
    {
        return $this->parent;
    }

    public function setParent($parent)
    {
        $this->parent = $parent;
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
