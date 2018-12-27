<?php

namespace Claroline\CoreBundle\API\Serializer\Log\Connection;

use Claroline\AppBundle\API\Options;
use Claroline\AppBundle\API\SerializerProvider;
use Claroline\CoreBundle\Entity\Log\Connection\LogConnectAdminTool;
use JMS\DiExtraBundle\Annotation as DI;

/**
 * @DI\Service("claroline.serializer.log.connect.admin_tool")
 * @DI\Tag("claroline.serializer")
 */
class LogConnectAdminToolSerializer
{
    /** @var SerializerProvider */
    private $serializer;

    /**
     * LogConnectAdminToolSerializer constructor.
     *
     * @DI\InjectParams({
     *     "serializer" = @DI\Inject("claroline.api.serializer")
     * })
     *
     * @param SerializerProvider $serializer
     */
    public function __construct(SerializerProvider $serializer)
    {
        $this->serializer = $serializer; // bad
    }

    public function getClass()
    {
        return LogConnectAdminTool::class;
    }

    /**
     * @param LogConnectAdminTool $log
     * @param array               $options
     *
     * @return array
     */
    public function serialize(LogConnectAdminTool $log, array $options = [])
    {
        $serialized = [
            'id' => $log->getUuid(),
            'date' => $log->getConnectionDate()->format('Y-m-d\TH:i:s'),
            'duration' => $log->getDuration(),
            'user' => $this->serializer->serialize($log->getUser(), [Options::SERIALIZE_MINIMAL]),
            'tool' => $this->serializer->serialize($log->getTool(), [Options::SERIALIZE_MINIMAL]),
            'toolName' => $log->getToolName(),
        ];

        return $serialized;
    }
}
