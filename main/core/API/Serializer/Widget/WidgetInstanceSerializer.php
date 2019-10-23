<?php

namespace Claroline\CoreBundle\API\Serializer\Widget;

use Claroline\AppBundle\API\Options;
use Claroline\AppBundle\API\Serializer\SerializerTrait;
use Claroline\AppBundle\API\SerializerProvider;
use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Entity\DataSource;
use Claroline\CoreBundle\Entity\Widget\Type\AbstractWidget;
use Claroline\CoreBundle\Entity\Widget\Widget;
use Claroline\CoreBundle\Entity\Widget\WidgetInstance;
use Claroline\CoreBundle\Entity\Widget\WidgetInstanceConfig;

class WidgetInstanceSerializer
{
    use SerializerTrait;

    /** @var ObjectManager */
    private $om;

    /** @var SerializerProvider */
    private $serializer;

    /**
     * WidgetInstanceSerializer constructor.
     *
     * @param ObjectManager      $om
     * @param SerializerProvider $serializer
     */
    public function __construct(
        ObjectManager $om,
        SerializerProvider $serializer)
    {
        $this->om = $om;
        $this->serializer = $serializer;
    }

    public function getClass()
    {
        return WidgetInstance::class;
    }

    public function serialize(WidgetInstance $widgetInstance, array $options = []): array
    {
        $widget = $widgetInstance->getWidget();
        $dataSource = $widgetInstance->getDataSource();

        // retrieves the custom configuration of the widget if any
        $parameters = [];

        if ($widget->getClass()) {
            // loads configuration entity for the current instance
            $typeParameters = $this->om
                ->getRepository($widget->getClass())
                ->findOneBy(['widgetInstance' => $widgetInstance]);

            if ($typeParameters) {
                // serializes custom configuration
                $parameters = $this->serializer->serialize($typeParameters, $options);
            }
        }

        return [
            'id' => $widgetInstance->getUuid(),
            'type' => $widget->getName(),
            'source' => $dataSource ? $dataSource->getName() : null,
            'parameters' => $parameters,
        ];
    }

    public function deserialize($data, WidgetInstance $widgetInstance, array $options = []): WidgetInstance
    {
        if (!in_array(Options::REFRESH_UUID, $options)) {
            $this->sipe('id', 'setUuid', $data, $widgetInstance);
        } else {
            $widgetInstance->refreshUuid();
        }

        $widgetInstanceConfig = $widgetInstance->getWidgetInstanceConfigs()[0];

        if (!$widgetInstanceConfig || in_array(Options::REFRESH_UUID, $options)) {
            $widgetInstanceConfig = new WidgetInstanceConfig();
            $widgetInstanceConfig->setWidgetInstance($widgetInstance);
        }

        $this->sipe('type', 'setType', $data, $widgetInstanceConfig);

        /** @var Widget $widget */
        $widget = $this->om
            ->getRepository(Widget::class)
            ->findOneBy(['name' => $data['type']]);

        if ($widget) {
            $widgetInstance->setWidget($widget);

            // process custom configuration of the widget if any
            if ($data['parameters'] && $widget->getClass()) {
                // loads configuration entity for the current instance
                $typeParameters = $this->om
                    ->getRepository($widget->getClass())
                    ->findOneBy(['widgetInstance' => $widgetInstance]);

                $parametersClass = $widget->getClass();

                if (!$typeParameters || in_array(Options::REFRESH_UUID, $options)) {
                    // no existing parameters => initializes one

                    /** @var AbstractWidget $typeParameters */
                    $typeParameters = new $parametersClass();
                }

                // deserializes custom config and link it to the instance
                $typeParameters = $this->serializer
                  ->get($parametersClass)
                  ->deserialize($data['parameters'], $typeParameters, $options);
                $typeParameters->setWidgetInstance($widgetInstance);

                // We either do this or cascade persist ¯\_(ツ)_/¯
                $this->om->persist($typeParameters);
                $this->om->persist($widgetInstance);
            }
        }

        if (!empty($data['source'])) {
            $dataSource = $this->om
                ->getRepository(DataSource::class)
                ->findOneBy(['name' => $data['source']]);

            $widgetInstance->setDataSource($dataSource);
        }

        return $widgetInstance;
    }
}
