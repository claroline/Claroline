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
     * @param ObjectManager      $om
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
        $contents = [];
        $arraySize = count($widgetContainer->getLayout());
        for ($i = 0; $i < $arraySize; ++$i) {
            $contents[$i] = null;
        }

        foreach ($widgetContainer->getInstances() as $widgetInstance) {
            $contents[$widgetInstance->getPosition()] = $this->serializer->serialize($widgetInstance, $options);
        }

        return [
            'id' => $this->getUuid($widgetContainer, $options),
            'name' => $widgetContainer->getName(),
            'display' => $this->serializeDisplay($widgetContainer),
            'contents' => $contents,
        ];
    }

    public function serializeDisplay(WidgetContainer $widgetContainer)
    {
        $display = [
          'layout' => $widgetContainer->getLayout(),
          'color' => $widgetContainer->getColor(),
          'backgroundType' => $widgetContainer->getBackgroundType(),
          'background' => $widgetContainer->getBackground(),
      ];
        if ('image' === $widgetContainer->getBackgroundType() && $widgetContainer->getBackground()) {
            $file = $this->om
              ->getRepository('Claroline\CoreBundle\Entity\File\PublicFile')
              ->findOneBy(['url' => $widgetContainer->getBackground()]);

            if ($file) {
                $display['background'] = $this->serializer->serialize($file);
            }
        } else {
            $display['background'] = $widgetContainer->getBackground();
        }

        return $display;
    }

    public function deserialize($data, WidgetContainer $widgetContainer, array $options): WidgetContainer
    {
        $this->sipe('id', 'setUuid', $data, $widgetContainer);
        $this->sipe('name', 'setName', $data, $widgetContainer);

        $this->sipe('display.layout', 'setLayout', $data, $widgetContainer);
        $this->sipe('display.color', 'setColor', $data, $widgetContainer);
        $this->sipe('display.backgroundType', 'setBackgroundType', $data, $widgetContainer);

        $display = $data['display'];

        if (isset($display['background']) && isset($display['background']['url'])) {
            $this->sipe('display.background.url', 'setBackground', $data, $widgetContainer);
        } else {
            $this->sipe('display.background', 'setBackground', $data, $widgetContainer);
        }

        if (isset($data['contents'])) {
            foreach ($data['contents'] as $index => $content) {
                if ($content) {
                    /** @var WidgetInstance $widgetInstance */
                    $widgetInstance = $this->serializer->deserialize(WidgetInstance::class, $content, $options);
                    $widgetInstance->setPosition($index);
                    $widgetContainer->addInstance($widgetInstance);

                    // We either do this or cascade persist ¯\_(ツ)_/¯
                    $this->om->persist($widgetInstance);
                }
            }
        }

        // todo : remove superfluous

        return $widgetContainer;
    }
}
