<?php

namespace Claroline\CoreBundle\API\Serializer\Widget;

use Claroline\AppBundle\API\Serializer\SerializerTrait;
use Claroline\AppBundle\API\SerializerProvider;
use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Entity\Widget\Type\AbstractWidget;
use Claroline\CoreBundle\Entity\Widget\Widget;
use Claroline\CoreBundle\Entity\Widget\WidgetInstance;
use JMS\DiExtraBundle\Annotation as DI;

/**
 * @DI\Service("claroline.serializer.widget_instance")
 * @DI\Tag("claroline.serializer")
 */
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
     * @DI\InjectParams({
     *     "om"         = @DI\Inject("claroline.persistence.object_manager"),
     *     "serializer" = @DI\Inject("claroline.api.serializer")
     * })
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
        return 'Claroline\CoreBundle\Entity\Widget\WidgetInstance';
    }

    public function serialize(WidgetInstance $widgetInstance, array $options = [])
    {
        $widget = $widgetInstance->getWidget();

        // retrieves the custom configuration of the widget if any
        $parameters = [];
        if (!empty($widget->getClass())) {
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
            'source' => null, // todo
            'parameters' => $parameters,
        ];
    }

    public function deserialize($data, WidgetInstance $widgetInstance, array $options = [])
    {
        $this->sipe('id', 'setUuid', $data, $widgetInstance);

        /** @var Widget $widget */
        $widget = $this->om
            ->getRepository('ClarolineCoreBundle:Widget\Widget')
            ->findOneBy(['name' => $data['type']]);

        if (!empty($widget)) {
            $widgetInstance->setWidget($widget);

            // process custom configuration of the widget if any
            if (!empty($data['parameters']) && !empty($widget->getClass())) {
                // loads configuration entity for the current instance
                $typeParameters = $this->om
                    ->getRepository($widget->getClass())
                    ->findOneBy(['widgetInstance' => $widgetInstance]);

                if (empty($typeParameters)) {
                    // no existing parameters => initializes one
                    $parametersClass = $widget->getClass();

                    /** @var AbstractWidget $typeParameters */
                    $typeParameters = new $parametersClass();
                }

                // deserializes custom config and link it to the instance
                $this->serializer->deserialize($data['parameters'], $typeParameters, $options);
                $typeParameters->setWidgetInstance($widgetInstance);
            }
        }

        return $widgetInstance;
    }
}
