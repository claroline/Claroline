<?php

namespace Claroline\HomeBundle\Entity\Type;

use Claroline\AppBundle\Entity\Identifier\Id;
use Claroline\CoreBundle\Entity\Widget\WidgetContainer;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="claro_home_tab_widgets")
 */
class WidgetsTab extends AbstractTab
{
    use Id;

    /**
     * @ORM\ManyToMany(targetEntity="Claroline\CoreBundle\Entity\Widget\WidgetContainer", cascade={"persist", "remove"})
     * @ORM\JoinTable(name="claro_home_tab_widgets_containers",
     *      joinColumns={@ORM\JoinColumn(name="tab_id", referencedColumnName="id", onDelete="CASCADE")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="container_id", referencedColumnName="id", onDelete="CASCADE", unique=true)}
     * )
     *
     * @var WidgetContainer[]|ArrayCollection
     */
    private $widgetContainers;

    public function __construct()
    {
        $this->widgetContainers = new ArrayCollection();
    }

    public static function getType(): string
    {
        return 'widgets';
    }

    public function getWidgetContainers()
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

    public function addWidgetContainer(WidgetContainer $widgetContainer)
    {
        if (!$this->widgetContainers->contains($widgetContainer)) {
            $this->widgetContainers->add($widgetContainer);
        }
    }

    public function removeWidgetContainer(WidgetContainer $widgetContainer)
    {
        if ($this->widgetContainers->contains($widgetContainer)) {
            $this->widgetContainers->removeElement($widgetContainer);
        }
    }
}
