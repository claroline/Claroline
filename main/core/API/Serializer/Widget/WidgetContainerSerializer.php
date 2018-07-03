<?php

namespace Claroline\CoreBundle\API\Serializer\Widget;

use Claroline\AppBundle\API\Serializer\SerializerTrait;
use Claroline\AppBundle\API\SerializerProvider;
use Claroline\CoreBundle\Entity\Widget\WidgetContainer;
use Claroline\CoreBundle\Entity\Widget\WidgetInstance;
use JMS\DiExtraBundle\Annotation as DI;

/**
 * @DI\Service("claroline.serializer.widget_container")
 * @DI\Tag("claroline.serializer")
 */
class WidgetContainerSerializer
{
    use SerializerTrait;

    /** @var SerializerProvider */
    private $serializer;

    /**
     * WidgetContainerSerializer constructor.
     *
     * @DI\InjectParams({
     *     "serializer" = @DI\Inject("claroline.api.serializer")
     * })
     *
     * @param SerializerProvider $serializer
     */
    public function __construct(
        SerializerProvider $serializer)
    {
        $this->serializer = $serializer;
    }

    public function getClass()
    {
        return 'Claroline\CoreBundle\Entity\Widget\WidgetContainer';
    }

    public function serialize(WidgetContainer $widgetContainer, array $options = [])
    {
        return [
            'id' => $widgetContainer->getUuid(),
            'name' => $widgetContainer->getName(),
            'display' => [
                'layout' => $widgetContainer->getLayout(),
                'color' => $widgetContainer->getColor(),
                'backgroundType' => $widgetContainer->getBackgroundType(),
                'background' => $widgetContainer->getBackground(),
            ],
            'contents' => array_map(function (WidgetInstance $widgetInstance) use ($options) {
                return $this->serializer->serialize($widgetInstance, $options);
            }, $widgetContainer->getInstances()->toArray()),
        ];
    }

    public function deserialize($data, WidgetContainer $widgetContainer, array $options)
    {
        $this->sipe('id', 'setUuid', $data, $widgetContainer);
        $this->sipe('name', 'setName', $data, $widgetContainer);

        $this->sipe('display.layout', 'setLayout', $data, $widgetContainer);
        $this->sipe('display.color', 'setColor', $data, $widgetContainer);
        $this->sipe('display.backgroundType', 'setBackgroundType', $data, $widgetContainer);
        $this->sipe('display.background', 'setBackground', $data, $widgetContainer);

        // todo deserialize instances
        if (isset($data['contents'])) {
            foreach ($data['contents'] as $index => $content) {
                /** @var WidgetInstance $widgetInstance */
                $widgetInstance = $this->serializer->deserialize('Claroline\CoreBundle\Entity\Widget\WidgetInstance', $content, $options);
                $widgetInstance->setPosition($index);
                $widgetContainer->addInstance($widgetInstance);
            }
        }

        return $widgetContainer;
    }
}
