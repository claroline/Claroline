<?php

namespace Claroline\CoreBundle\API\Serializer\Widget\Type;

use Claroline\AppBundle\API\Options;
use Claroline\AppBundle\API\Serializer\SerializerTrait;
use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\API\Serializer\Resource\ResourceNodeSerializer;
use Claroline\CoreBundle\Entity\Resource\ResourceNode;
use Claroline\CoreBundle\Entity\Widget\Type\ResourceWidget;
use JMS\DiExtraBundle\Annotation as DI;

/**
 * @DI\Service("claroline.serializer.widget_resource")
 * @DI\Tag("claroline.serializer")
 */
class ResourceWidgetSerializer
{
    use SerializerTrait;

    /** @var ObjectManager */
    private $om;

    /** @var ResourceNodeSerializer */
    private $nodeSerializer;

    /**
     * WidgetInstanceSerializer constructor.
     *
     * @DI\InjectParams({
     *     "om"             = @DI\Inject("claroline.persistence.object_manager"),
     *     "nodeSerializer" = @DI\Inject("claroline.serializer.resource_node")
     * })
     *
     * @param ObjectManager          $om
     * @param ResourceNodeSerializer $nodeSerializer
     */
    public function __construct(
        ObjectManager $om,
        ResourceNodeSerializer $nodeSerializer)
    {
        $this->om = $om;
        $this->nodeSerializer = $nodeSerializer;
    }

    /**
     * @return string
     */
    public function getClass(): string
    {
        return ResourceWidget::class;
    }

    public function serialize(ResourceWidget $widget, array $options = []): array
    {
        return [
            'resource' => $widget->getResourceNode() ? $this->nodeSerializer->serialize($widget->getResourceNode(), [Options::SERIALIZE_MINIMAL]) : null,
            'showResourceHeader' => $widget->getShowResourceHeader(),
        ];
    }

    public function deserialize($data, ResourceWidget $widget, array $options = []): ResourceWidget
    {
        if (isset($data['resource'])) {
            $resourceNode = $this->om
                ->getRepository(ResourceNode::class)
                ->findOneBy(['uuid' => $data['resource']['id']]);

            if ($resourceNode) {
                $widget->setResourceNode($resourceNode);
            }
        }
        $this->sipe('showResourceHeader', 'setShowResourceHeader', $data, $widget);

        return $widget;
    }
}
