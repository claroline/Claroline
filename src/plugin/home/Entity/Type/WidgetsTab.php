<?php

namespace Claroline\HomeBundle\Entity\Type;

use Claroline\AppBundle\Entity\Identifier\Id;
use Claroline\CoreBundle\Entity\Widget\WidgetContainer;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Table(name: 'claro_home_tab_widgets')]
#[ORM\Entity]
class WidgetsTab extends AbstractTab
{
    use Id;

    /**
     * @var Collection<int, WidgetContainer>
     */
    #[ORM\JoinTable(name: 'claro_home_tab_widgets_containers')]
    #[ORM\JoinColumn(name: 'tab_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    #[ORM\InverseJoinColumn(name: 'container_id', referencedColumnName: 'id', onDelete: 'CASCADE', unique: true)]
    #[ORM\ManyToMany(targetEntity: WidgetContainer::class, cascade: ['persist', 'remove'])]
    private Collection $widgetContainers;

    public function __construct()
    {
        $this->widgetContainers = new ArrayCollection();
    }

    public static function getType(): string
    {
        return 'widgets';
    }

    public function getWidgetContainers(): Collection
    {
        return $this->widgetContainers;
    }

    public function getWidgetContainer(string $containerId): ?WidgetContainer
    {
        $found = null;

        foreach ($this->widgetContainers as $container) {
            if ($container->getUuid() === $containerId) {
                $found = $container;
                break;
            }
        }

        return $found;
    }

    public function addWidgetContainer(WidgetContainer $widgetContainer): void
    {
        if (!$this->widgetContainers->contains($widgetContainer)) {
            $this->widgetContainers->add($widgetContainer);
        }
    }

    public function removeWidgetContainer(WidgetContainer $widgetContainer): void
    {
        if ($this->widgetContainers->contains($widgetContainer)) {
            $this->widgetContainers->removeElement($widgetContainer);
        }
    }
}
