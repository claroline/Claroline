<?php

namespace Claroline\CoreBundle\API\Serializer\Workspace;

use Claroline\CoreBundle\Entity\Tool\OrderedTool;
use JMS\DiExtraBundle\Annotation as DI;

/**
 * @DI\Service("claroline.serializer.ordered_tool")
 * @DI\Tag("claroline.serializer")
 */
class OrderedToolSerializer
{
    /** @var ToolSerializer */
    private $toolSerializer;

    /**
     * PendingRegistrationSerializer constructor.
     *
     * @DI\InjectParams({
     *     "toolSerializer" = @DI\Inject("claroline.serializer.tool")
     * })
     *
     * @param ToolSerializer $toolSerializer
     */
    public function __construct(ToolSerializer $toolSerializer)
    {
        $this->toolSerializer = $toolSerializer;
    }

    public function getClass()
    {
        return OrderedTool::class;
    }

    public function serialize(OrderedTool $orderedTool): array
    {
        return [
          //maybe remove tools. See later
          'id' => $orderedTool->getId(),
          'tool' => $this->toolSerializer->serialize($orderedTool->getTool()),
          'position' => $orderedTool->getOrder(),
          'restrictions' => $this->serializeRestrictions($orderedTool),
        ];
    }

    private function serializeRestrictions(OrderedTool $orderedTool): array
    {
        return [];
    }
}
