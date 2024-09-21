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

use Doctrine\DBAL\Types\Types;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Entity\Workspace\Workspace;
use Claroline\AppBundle\Entity\Identifier\Id;
use Doctrine\ORM\Mapping as ORM;

/**
 *
 * @todo merge with WidgetInstance entity.
 */
#[ORM\Table(name: 'claro_widget_instance_config')]
#[ORM\Entity]
class WidgetInstanceConfig
{
    use Id;

    #[ORM\JoinColumn(name: 'widget_instance_id', onDelete: 'CASCADE', nullable: true)]
    #[ORM\ManyToOne(targetEntity: WidgetInstance::class, inversedBy: 'widgetInstanceConfigs', cascade: ['persist', 'remove'])]
    private ?WidgetInstance $widgetInstance = null;

    #[ORM\JoinColumn(name: 'user_id', nullable: true, onDelete: 'CASCADE')]
    #[ORM\ManyToOne(targetEntity: User::class)]
    private ?User $user = null;

    #[ORM\JoinColumn(name: 'workspace_id', nullable: true, onDelete: 'CASCADE')]
    #[ORM\ManyToOne(targetEntity: Workspace::class)]
    private ?Workspace $workspace = null;

    #[ORM\Column(name: 'widget_order', type: Types::INTEGER)]
    private $widgetOrder = 0;

    #[ORM\Column]
    private $type;

    #[ORM\Column(type: Types::BOOLEAN, name: 'is_visible')]
    private $visible = true;

    #[ORM\Column(type: Types::BOOLEAN, name: 'is_locked')]
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
