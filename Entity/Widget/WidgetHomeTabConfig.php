<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Entity\Widget;

use Claroline\CoreBundle\Entity\Home\HomeTab;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="Claroline\CoreBundle\Repository\WidgetHomeTabConfigRepository")
 * @ORM\Table(
 *     name="claro_widget_home_tab_config"
 * )
 */
class WidgetHomeTabConfig
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\ManyToOne(
     *     targetEntity="Claroline\CoreBundle\Entity\Widget\WidgetInstance"
     * )
     * @ORM\JoinColumn(name="widget_instance_id", onDelete="CASCADE", nullable=true)
     */
    protected $widgetInstance;

    /**
     * @ORM\ManyToOne(
     *     targetEntity="Claroline\CoreBundle\Entity\Home\HomeTab",
     *     inversedBy="widgetHomeTabConfigs"
     * )
     * @ORM\JoinColumn(name="home_tab_id", onDelete="CASCADE", nullable=false)
     */
    protected $homeTab;

    /**
     * @ORM\ManyToOne(
     *     targetEntity="Claroline\CoreBundle\Entity\User"
     * )
     * @ORM\JoinColumn(name="user_id", nullable=true, onDelete="CASCADE")
     */
    protected $user;

    /**
     * @ORM\ManyToOne(
     *     targetEntity="Claroline\CoreBundle\Entity\Workspace\Workspace"
     * )
     * @ORM\JoinColumn(name="workspace_id", nullable=true, onDelete="CASCADE")
     */
    protected $workspace;

    /**
     * @ORM\Column(name="widget_order", type="integer")
     */
    protected $widgetOrder;

    /**
     * @ORM\Column()
     */
    protected $type;

    /**
     * @ORM\Column(type="boolean", name="is_visible")
     */
    protected $visible = true;

    /**
     * @ORM\Column(type="boolean", name="is_locked")
     */
    protected $locked = false;

    public function getId()
    {
        return $this->id;
    }

    public function setId($id)
    {
        $this->id = $id;
    }

    public function getWidgetInstance()
    {
        return $this->widgetInstance;
    }

    public function setWidgetInstance(WidgetInstance $widgetInstance)
    {
        $this->widgetInstance = $widgetInstance;
    }

    public function getHomeTab()
    {
        return $this->homeTab;
    }

    public function setHomeTab(HomeTab $homeTab)
    {
        $this->homeTab = $homeTab;
    }

    public function getUser()
    {
        return $this->user;
    }

    public function setUser($user)
    {
        $this->user = $user;
    }

    public function getWorkspace()
    {
        return $this->workspace;
    }

    public function setWorkspace($workspace)
    {
        $this->workspace = $workspace;
    }

    public function getWidgetOrder()
    {
        return $this->widgetOrder;
    }

    public function setWidgetOrder($widgetOrder)
    {
        $this->widgetOrder = $widgetOrder;
    }

    public function getType()
    {
        return $this->type;
    }

    public function setType($type)
    {
        $this->type = $type;
    }

    public function isVisible()
    {
        return $this->visible;
    }

    public function setVisible($visible)
    {
        $this->visible = $visible;
    }

    public function isLocked()
    {
        return $this->locked;
    }

    public function setLocked($locked)
    {
        $this->locked = $locked;
    }
}
