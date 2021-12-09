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

use Claroline\AppBundle\Entity\Identifier\Id;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity()
 * @ORM\Table(
 *     name="claro_widget_instance_config"
 * )
 *
 * @todo merge with WidgetInstance entity.
 */
class WidgetInstanceConfig
{
    use Id;

    /**
     * @ORM\ManyToOne(
     *     targetEntity="Claroline\CoreBundle\Entity\Widget\WidgetInstance",
     *     inversedBy="widgetInstanceConfigs",
     *     cascade={"persist", "remove"}
     * )
     * @ORM\JoinColumn(name="widget_instance_id", onDelete="CASCADE", nullable=true)
     */
    private $widgetInstance;

    /**
     * @ORM\ManyToOne(
     *     targetEntity="Claroline\CoreBundle\Entity\User"
     * )
     * @ORM\JoinColumn(name="user_id", nullable=true, onDelete="CASCADE")
     */
    private $user;

    /**
     * @ORM\ManyToOne(
     *     targetEntity="Claroline\CoreBundle\Entity\Workspace\Workspace"
     * )
     * @ORM\JoinColumn(name="workspace_id", nullable=true, onDelete="CASCADE")
     */
    private $workspace;

    /**
     * @ORM\Column(name="widget_order", type="integer")
     */
    private $widgetOrder = 0;

    /**
     * @ORM\Column()
     */
    private $type;

    /**
     * @ORM\Column(type="boolean", name="is_visible")
     */
    private $visible = true;

    /**
     * @ORM\Column(type="boolean", name="is_locked")
     */
    private $locked = false;

    public function getWidgetInstance()
    {
        return $this->widgetInstance;
    }

    public function setWidgetInstance(WidgetInstance $widgetInstance)
    {
        $this->widgetInstance = $widgetInstance;
        $widgetInstance->addWidgetInstanceConfig($this);
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

    /* alias */
    public function getPosition()
    {
        return $this->getWidgetOrder();
    }

    /* alias */
    public function setPosition($widgetOrder)
    {
        $this->setWidgetOrder($widgetOrder);
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
