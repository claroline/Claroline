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
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Entity\Workspace\Workspace;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

/**
 * @todo merge with WidgetInstance entity.
 */
#[ORM\Table(name: 'claro_widget_instance_config')]
#[ORM\Entity]
class WidgetInstanceConfig
{
    use Id;

    #[ORM\ManyToOne(targetEntity: WidgetInstance::class, cascade: ['persist', 'remove'], inversedBy: 'widgetInstanceConfigs')]
    #[ORM\JoinColumn(name: 'widget_instance_id', nullable: true, onDelete: 'CASCADE')]
    private ?WidgetInstance $widgetInstance = null;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(name: 'user_id', nullable: true, onDelete: 'CASCADE')]
    private ?User $user = null;

    #[ORM\ManyToOne(targetEntity: Workspace::class)]
    #[ORM\JoinColumn(name: 'workspace_id', nullable: true, onDelete: 'CASCADE')]
    private ?Workspace $workspace = null;

    #[ORM\Column(name: 'widget_order', type: Types::INTEGER)]
    private int $widgetOrder = 0;

    #[ORM\Column]
    private ?string $type = null;

    #[ORM\Column(name: 'is_visible', type: Types::BOOLEAN)]
    private bool $visible = true;

    #[ORM\Column(name: 'is_locked', type: Types::BOOLEAN)]
    private bool $locked = false;

    public function getWidgetInstance(): ?WidgetInstance
    {
        return $this->widgetInstance;
    }

    public function setWidgetInstance(WidgetInstance $widgetInstance): void
    {
        $this->widgetInstance = $widgetInstance;
        $widgetInstance->addWidgetInstanceConfig($this);
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): void
    {
        $this->user = $user;
    }

    public function getWorkspace(): ?Workspace
    {
        return $this->workspace;
    }

    public function setWorkspace(?Workspace $workspace): void
    {
        $this->workspace = $workspace;
    }

    public function getWidgetOrder(): int
    {
        return $this->widgetOrder;
    }

    public function setWidgetOrder(int $widgetOrder): void
    {
        $this->widgetOrder = $widgetOrder;
    }

    /* alias */
    public function getPosition(): int
    {
        return $this->getWidgetOrder();
    }

    /* alias */
    public function setPosition(int $widgetOrder): int
    {
        $this->setWidgetOrder($widgetOrder);
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(?string $type): void
    {
        $this->type = $type;
    }

    public function isVisible(): bool
    {
        return $this->visible;
    }

    public function setVisible(bool $visible): void
    {
        $this->visible = $visible;
    }

    public function isLocked(): bool
    {
        return $this->locked;
    }

    public function setLocked(bool $locked): void
    {
        $this->locked = $locked;
    }
}
