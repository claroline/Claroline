<?php

namespace Claroline\CoreBundle\Entity\Widget;

use Claroline\AppBundle\Entity\Identifier\Id;
use Claroline\AppBundle\Entity\Identifier\Uuid;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * WidgetContainer entity.
 */
#[ORM\Table(name: 'claro_widget_container')]
#[ORM\Entity]
class WidgetContainer
{
    use Id;
    use Uuid;

    /**
     * The list of content instances.
     *
     *
     * @var ArrayCollection|WidgetInstance[]
     */
    #[ORM\OneToMany(targetEntity: WidgetInstance::class, mappedBy: 'container', cascade: ['persist', 'remove', 'refresh'])]
    private $instances;

    /**
     * @var WidgetContainerConfig[]
     */
    #[ORM\OneToMany(targetEntity: WidgetContainerConfig::class, mappedBy: 'widgetContainer', cascade: ['persist', 'remove'])]
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

    public function getInstance(string $instanceId): ?WidgetInstance
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
