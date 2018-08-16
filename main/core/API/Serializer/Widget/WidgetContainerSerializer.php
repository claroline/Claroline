<?php

namespace Claroline\CoreBundle\API\Serializer\Widget;

use Claroline\AppBundle\API\Serializer\SerializerTrait;
use Claroline\AppBundle\API\SerializerProvider;
use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Entity\Widget\WidgetContainer;
use Claroline\CoreBundle\Entity\Widget\WidgetContainerConfig;
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
        $widgetContainerConfig = $widgetContainer->getWidgetContainerConfigs()[0];

        $contents = [];
        $arraySize = count($widgetContainerConfig->getLayout());

        for ($i = 0; $i < $arraySize; ++$i) {
            $contents[$i] = null;
        }

        foreach ($widgetContainer->getInstances() as $widgetInstance) {
            $config = $widgetInstance->getWidgetInstanceConfigs()[0];

            if ($config) {
                $contents[$config->getPosition()] = $this->serializer->serialize($widgetInstance, $options);
            }
        }

        return [
            'id' => $this->getUuid($widgetContainer, $options),
            'name' => $widgetContainerConfig->getName(),
            'display' => $this->serializeDisplay($widgetContainerConfig),
            'contents' => $contents,
        ];
    }

    public function serializeDisplay(WidgetContainerConfig $widgetContainerConfig)
    {
        $display = [
            'layout' => $widgetContainerConfig->getLayout(),
            'color' => $widgetContainerConfig->getColor(),
            'backgroundType' => $widgetContainerConfig->getBackgroundType(),
            'background' => $widgetContainerConfig->getBackground(),
        ];

        if ('image' === $widgetContainerConfig->getBackgroundType() && $widgetContainerConfig->getBackground()) {
            $file = $this->om
              ->getRepository('Claroline\CoreBundle\Entity\File\PublicFile')
              ->findOneBy(['url' => $widgetContainerConfig->getBackground()]);

            if ($file) {
                $display['background'] = $this->serializer->serialize($file);
            }
        } else {
            $display['background'] = $widgetContainerConfig->getBackground();
        }

        return $display;
    }

    public function deserialize($data, WidgetContainer $widgetContainer, array $options): WidgetContainer
    {
        $widgetContainerConfig = $this->om->getRepository(WidgetContainerConfig::class)
          ->findOneBy(['widgetContainer' => $widgetContainer]);

        if (!$widgetContainerConfig) {
            $widgetContainerConfig = new WidgetContainerConfig();
            $widgetContainerConfig->setWidgetContainer($widgetContainer);
            $this->om->persist($widgetContainerConfig);
            $this->om->persist($widgetContainer);
        }

        $this->sipe('id', 'setUuid', $data, $widgetContainer);
        $this->sipe('name', 'setName', $data, $widgetContainerConfig);

        $this->sipe('display.layout', 'setLayout', $data, $widgetContainerConfig);
        $this->sipe('display.color', 'setColor', $data, $widgetContainerConfig);
        $this->sipe('display.backgroundType', 'setBackgroundType', $data, $widgetContainerConfig);

        $display = $data['display'];

        if (isset($display['background']) && isset($display['background']['url'])) {
            $this->sipe('display.background.url', 'setBackground', $data, $widgetContainerConfig);
        } else {
            $this->sipe('display.background', 'setBackground', $data, $widgetContainerConfig);
        }

        if (isset($data['contents'])) {
            foreach ($data['contents'] as $index => $content) {
                if ($content) {
                    /** @var WidgetInstance $widgetInstance */
                    $widgetInstance = $this->serializer->deserialize(WidgetInstance::class, $content, $options);
                    $widgetInstanceConfig = $widgetInstance->getWidgetInstanceConfigs()[0];
                    $widgetInstanceConfig->setPosition($index);
                    $widgetInstance->setContainer($widgetContainer);

                    // We either do this or cascade persist ¯\_(ツ)_/¯
                    $this->om->persist($widgetInstance);
                    $this->om->persist($widgetInstanceConfig);
                }
            }
        }

        // todo : remove superfluous (or maybe not it looks ok as is)

        return $widgetContainer;
    }

    public function getConfig(WidgetContainer $container, array $options)
    {
    }
}
