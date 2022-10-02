<?php

namespace Claroline\CoreBundle\API\Serializer\Tool;

use Claroline\AppBundle\API\Serializer\SerializerInterface;
use Claroline\AppBundle\API\Serializer\SerializerTrait;
use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Entity\Tool\OrderedTool;
use Claroline\CoreBundle\Manager\Tool\ToolManager;

class OrderedToolSerializer
{
    use SerializerTrait;

    /** @var ObjectManager */
    private $om;
    /** @var ToolManager */
    private $toolManager;

    public function __construct(
        ObjectManager $om,
        ToolManager $toolManager
    ) {
        $this->om = $om;
        $this->toolManager = $toolManager;
    }

    public function getClass()
    {
        return OrderedTool::class;
    }

    public function getName()
    {
        return 'ordered_tool';
    }

    public function serialize(OrderedTool $orderedTool, ?array $options = []): array
    {
        $serialized = [
            'id' => $orderedTool->getUuid(),
            'name' => $orderedTool->getTool()->getName(),
            'icon' => $orderedTool->getTool()->getClass(),
            'poster' => $orderedTool->getPoster(),
            'thumbnail' => $orderedTool->getThumbnail(),
            'display' => [
                'order' => $orderedTool->getOrder(),
                'showIcon' => $orderedTool->getShowIcon(),
                'fullscreen' => $orderedTool->getFullscreen(),
            ],
            'restrictions' => [
                'hidden' => $orderedTool->isHidden(),
            ],
        ];

        if (!in_array(SerializerInterface::SERIALIZE_TRANSFER, $options)) {
            $serialized['permissions'] = $this->toolManager->getCurrentPermissions($orderedTool);
        }

        return $serialized;
    }

    public function deserialize(array $data, OrderedTool $orderedTool, ?array $options = []): OrderedTool
    {
        if (!in_array(SerializerInterface::REFRESH_UUID, $options)) {
            $this->sipe('id', 'setUuid', $data, $orderedTool);
        } else {
            $orderedTool->refreshUuid();
        }

        $this->sipe('display.order', 'setOrder', $data, $orderedTool);
        $this->sipe('display.showIcon', 'setShowIcon', $data, $orderedTool);
        $this->sipe('display.fullscreen', 'setFullscreen', $data, $orderedTool);
        $this->sipe('restrictions.hidden', 'setHidden', $data, $orderedTool);
        $this->sipe('poster', 'setPoster', $data, $orderedTool);
        $this->sipe('thumbnail', 'setThumbnail', $data, $orderedTool);

        return $orderedTool;
    }
}
