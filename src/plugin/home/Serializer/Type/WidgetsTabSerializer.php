<?php

namespace Claroline\HomeBundle\Serializer\Type;

use Claroline\AppBundle\API\Serializer\SerializerTrait;
use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\API\Serializer\Widget\WidgetContainerSerializer;
use Claroline\CoreBundle\Entity\Widget\WidgetContainer;
use Claroline\HomeBundle\Entity\Type\WidgetsTab;

class WidgetsTabSerializer
{
    use SerializerTrait;

    /** @var ObjectManager */
    private $om;
    /** @var WidgetContainerSerializer */
    private $widgetContainerSerializer;

    public function __construct(
        ObjectManager $om,
        WidgetContainerSerializer $widgetContainerSerializer
    ) {
        $this->om = $om;
        $this->widgetContainerSerializer = $widgetContainerSerializer;
    }

    public function getName()
    {
        return 'home_widgets_tab';
    }

    public function getClass()
    {
        return WidgetsTab::class;
    }

    public function serialize(WidgetsTab $tab, array $options = []): array
    {
        $containers = [];
        foreach ($tab->getWidgetContainers() as $container) {
            $widgetContainerConfig = $container->getWidgetContainerConfigs()[0];
            if ($widgetContainerConfig) {
                if (!array_key_exists($widgetContainerConfig->getPosition(), $containers)) {
                    $containers[$widgetContainerConfig->getPosition()] = $container;
                } else {
                    $containers[] = $container;
                }
            }
        }

        ksort($containers);
        $containers = array_values($containers);

        return [
            'widgets' => array_map(function ($container) use ($options) {
                return $this->widgetContainerSerializer->serialize($container, $options);
            }, $containers),
        ];
    }

    public function deserialize(array $data, WidgetsTab $tab, array $options = []): WidgetsTab
    {
        if (isset($data['widgets'])) {
            /** @var WidgetContainer[] $currentContainers */
            $currentContainers = $tab->getWidgetContainers()->toArray();
            $containerIds = [];

            // update containers
            foreach ($data['widgets'] as $position => $widgetContainerData) {
                if (isset($widgetContainerData['id'])) {
                    $widgetContainer = $tab->getWidgetContainer($widgetContainerData['id']);
                }

                if (empty($widgetContainer)) {
                    $widgetContainer = new WidgetContainer();
                }

                $this->widgetContainerSerializer->deserialize($widgetContainerData, $widgetContainer, $options);
                $tab->addWidgetContainer($widgetContainer);

                $widgetContainerConfig = $widgetContainer->getWidgetContainerConfigs()[0];
                $widgetContainerConfig->setPosition($position);
                $containerIds[] = $widgetContainer->getUuid();
            }

            // removes containers which no longer exists
            foreach ($currentContainers as $currentContainer) {
                if (!in_array($currentContainer->getUuid(), $containerIds)) {
                    $tab->removeWidgetContainer($currentContainer);
                    $this->om->remove($currentContainer);
                }
            }
        }

        return $tab;
    }
}
