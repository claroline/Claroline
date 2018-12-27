<?php

namespace Claroline\CoreBundle\API\Serializer\Log\Connection;

use Claroline\AppBundle\API\Options;
use Claroline\AppBundle\API\SerializerProvider;
use Claroline\CoreBundle\Entity\Log\Connection\LogConnectTool;
use JMS\DiExtraBundle\Annotation as DI;

/**
 * @DI\Service("claroline.serializer.log.connect.workspace_tool")
 * @DI\Tag("claroline.serializer")
 */
class LogConnectToolSerializer
{
    /** @var SerializerProvider */
    private $serializer;

    /**
     * LogConnectToolSerializer constructor.
     *
     * @DI\InjectParams({
     *     "serializer" = @DI\Inject("claroline.api.serializer")
     * })
     *
     * @param SerializerProvider $serializer
     */
    public function __construct(SerializerProvider $serializer)
    {
        $this->serializer = $serializer;
    }

    public function getClass()
    {
        return LogConnectTool::class;
    }

    /**
     * @param LogConnectTool $log
     * @param array          $options
     *
     * @return array
     */
    public function serialize(LogConnectTool $log, array $options = [])
    {
        $serialized = [
            'id' => $log->getUuid(),
            'date' => $log->getConnectionDate()->format('Y-m-d\TH:i:s'),
            'duration' => $log->getDuration(),
            'user' => $this->serializer->serialize($log->getUser(), [Options::SERIALIZE_MINIMAL]),
            'tool' => $this->serializer->serialize($log->getTool(), [Options::SERIALIZE_MINIMAL]),
            'toolName' => $log->getToolName(),
            'originalToolName' => $log->getOrignalToolName(),
            'workspace' => $log->getWorkspace() ?
                $this->serializer->serialize($log->getWorkspace(), [Options::SERIALIZE_MINIMAL]) :
                null,
            'workspaceName' => $log->getWorkspaceName(),
        ];

        return $serialized;
    }
}
