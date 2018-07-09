<?php

namespace Claroline\CoreBundle\API\Serializer\Widget;

use Claroline\AppBundle\API\Serializer\SerializerTrait;
use Claroline\AppBundle\API\SerializerProvider;
use Claroline\AppBundle\Persistence\ObjectManager;
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

    /** @var ObjectManager */
    private $om;

    /**
     * WidgetContainerSerializer constructor.
     *
     * @DI\InjectParams({
     *     "serializer" = @DI\Inject("claroline.api.serializer"),
     *    "om"          = @DI\Inject("claroline.persistence.object_manager")
     * })
     *
     * @param SerializerProvider $serializer
     */
    public function __construct(
        ObjectManager $om,
        SerializerProvider $serializer
    ) {
        $this->om = $om;
        $this->serializer = $serializer;
    }

    public function getClass()
    {
        return WidgetContainer::class;
    }

    public function serialize(WidgetContainer $widgetContainer, array $options = []): array
    {
        return [
            'id' => $this->getUuid($widgetContainer, $options),
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

    public function deserialize($data, WidgetContainer $widgetContainer, array $options): WidgetContainer
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
                $widgetInstance = $this->serializer->deserialize(WidgetInstance::class, $content, $options);
                $widgetInstance->setPosition($index);
                $widgetContainer->addInstance($widgetInstance);

                // We either do this or cascade persist ¯\_(ツ)_/¯
                $this->om->persist($widgetInstance);
            }
        }

        return $widgetContainer;
    }
}
