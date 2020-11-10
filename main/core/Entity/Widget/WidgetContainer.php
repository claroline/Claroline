<?php

namespace Claroline\CoreBundle\Entity\Widget;

use Claroline\AppBundle\Entity\Identifier\Id;
use Claroline\AppBundle\Entity\Identifier\Uuid;
use Claroline\HomeBundle\Entity\HomeTab;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * WidgetContainer entity.
 *
 * @ORM\Entity()
 * @ORM\Table(name="claro_widget_container")
 *
 * @todo : remove dependency to HomeBundle
 */
class WidgetContainer
{
    use Id;
    use Uuid;

    /**
     * The list of content instances.
     *
     * @ORM\OneToMany(
     *     targetEntity="Claroline\CoreBundle\Entity\Widget\WidgetInstance",
     *     mappedBy="container",
     *     cascade={"persist", "remove", "refresh"}
     * )
     *
     * @var ArrayCollection|WidgetInstance[]
     */
    private $instances;

    /**
     * The list of content instances.
     *
     * @ORM\ManyToOne(
     *    targetEntity="Claroline\HomeBundle\Entity\HomeTab",
     *    inversedBy="widgetContainers"
     * )
     * @ORM\JoinColumn(name="hometab_id", onDelete="CASCADE", nullable=true)
     * @ORM\OrderBy({"position" = "ASC"})
     *
     * @var HomeTab
     */
    private $homeTab;

    /**
     * @ORM\OneToMany(
     *     targetEntity="Claroline\CoreBundle\Entity\Widget\WidgetContainerConfig",
     *     mappedBy="widgetContainer",
     *     cascade={"persist", "remove"}
     * )
     *
     * @var WidgetContainerConfig[]
     */
    protected $widgetContainerConfigs;

    /**
     * WidgetContainer constructor.
     */
    public function __construct()
    {
        $this->refreshUuid();

        $this->instances = new ArrayCollection();
        $this->widgetContainerConfigs = new ArrayCollection();
    }

    /**
     * Get the list of WidgetInstance in the container.
     *
     * @return ArrayCollection|WidgetInstance[]
     */
    public function getInstances()
    {
        return $this->instances;
    }

    /**
     * @param string $instanceId
     *
     * @return WidgetInstance|null
     */
    public function getInstance($instanceId)
    {
        $found = null;

        foreach ($this->instances as $instance) {
            if ($instance && $instance->getUuid() === $instanceId) {
                $found = $instance;
                break;
            }
        }

        return $found;
    }

    /**
     * Add a WidgetInstance into the container.
     */
    public function addInstance(WidgetInstance $instance)
    {
        if (!$this->instances->contains($instance)) {
            $this->instances->add($instance);
            $instance->setContainer($this);
        }
    }

    /**
     * Remove a WidgetInstance from the container.
     */
    public function removeInstance(WidgetInstance $instance)
    {
        if ($this->instances->contains($instance)) {
            $this->instances->removeElement($instance);
            $instance->setContainer(null);
        }
    }

    public function setHomeTab(HomeTab $homeTab = null)
    {
        if ($this->homeTab) {
            $this->homeTab->removeWidgetContainer($this);
        }

        if ($homeTab) {
            $this->homeTab = $homeTab;
            $this->homeTab->addWidgetContainer($this);
        }
    }

    public function getHomeTab()
    {
        return $this->homeTab;
    }

    public function getWidgetContainerConfigs()
    {
        return $this->widgetContainerConfigs;
    }

    public function addWidgetContainerConfig(WidgetContainerConfig $config)
    {
        if (!$this->widgetContainerConfigs->contains($config)) {
            $this->widgetContainerConfigs->add($config);
        }
    }

    public function removeWidgetContainerConfig(WidgetContainerConfig $config)
    {
        if ($this->widgetContainerConfigs->contains($config)) {
            $this->widgetContainerConfigs->removeElement($config);
        }
    }
}
