<?php

namespace Claroline\CoreBundle\API\Serializer\Tool;

use Claroline\AppBundle\API\Serializer\SerializerTrait;
use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\API\Serializer\File\PublicFileSerializer;
use Claroline\CoreBundle\Entity\File\PublicFile;
use Claroline\CoreBundle\Entity\Tool\OrderedTool;
use Claroline\CoreBundle\Manager\Tool\ToolManager;

class OrderedToolSerializer
{
    use SerializerTrait;

    /** @var ObjectManager */
    private $om;
    /** @var ToolManager */
    private $toolManager;
    /** @var PublicFileSerializer */
    private $fileSerializer;

    /**
     * OrderedToolSerializer constructor.
     *
     * @param ObjectManager        $om
     * @param ToolManager          $toolManager
     * @param PublicFileSerializer $fileSerializer
     */
    public function __construct(
        ObjectManager $om,
        ToolManager $toolManager,
        PublicFileSerializer $fileSerializer
    ) {
        $this->om = $om;
        $this->toolManager = $toolManager;
        $this->fileSerializer = $fileSerializer;
    }

    public function getClass()
    {
        return OrderedTool::class;
    }

    public function getName()
    {
        return 'ordered_tool';
    }

    public function serialize(OrderedTool $orderedTool): array
    {
        return [
            'id' => $orderedTool->getUuid(),
            'name' => $orderedTool->getTool()->getName(),
            'icon' => $orderedTool->getTool()->getClass(),
            'poster' => $this->serializePoster($orderedTool),
            'thumbnail' => $this->serializeThumbnail($orderedTool),
            'permissions' => $this->toolManager->getCurrentPermissions($orderedTool),
            'display' => [
                'showIcon' => $orderedTool->getShowIcon(),
            ],
        ];
    }

    public function deserialize(array $data, OrderedTool $orderedTool): OrderedTool
    {
        $this->sipe('display.showIcon', 'setShowIcon', $data, $orderedTool);

        if (isset($data['poster']) && isset($data['poster']['url'])) {
            $orderedTool->setPoster($data['poster']['url']);
        }

        if (isset($data['thumbnail']) && isset($data['thumbnail']['url'])) {
            $orderedTool->setThumbnail($data['thumbnail']['url']);
        }

        return $orderedTool;
    }

    /**
     * Serialize the tool poster.
     *
     * @param OrderedTool $orderedTool
     *
     * @return array|null
     */
    private function serializePoster(OrderedTool $orderedTool)
    {
        if (!empty($orderedTool->getPoster())) {
            /** @var PublicFile $file */
            $file = $this->om
                ->getRepository(PublicFile::class)
                ->findOneBy(['url' => $orderedTool->getPoster()]);

            if ($file) {
                return $this->fileSerializer->serialize($file);
            }
        }

        return null;
    }

    /**
     * Serialize the tool thumbnail.
     *
     * @param OrderedTool $orderedTool
     *
     * @return array|null
     */
    private function serializeThumbnail(OrderedTool $orderedTool)
    {
        if (!empty($orderedTool->getThumbnail())) {
            /** @var PublicFile $file */
            $file = $this->om
                ->getRepository(PublicFile::class)
                ->findOneBy(['url' => $orderedTool->getThumbnail()]);

            if ($file) {
                return $this->fileSerializer->serialize($file);
            }
        }

        return null;
    }
}
