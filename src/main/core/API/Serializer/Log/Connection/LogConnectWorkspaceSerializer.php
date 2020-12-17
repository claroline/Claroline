<?php

namespace Claroline\CoreBundle\API\Serializer\Log\Connection;

use Claroline\AppBundle\API\Options;
use Claroline\AppBundle\API\SerializerProvider;
use Claroline\CoreBundle\Entity\Log\Connection\LogConnectWorkspace;

class LogConnectWorkspaceSerializer
{
    /** @var SerializerProvider */
    private $serializer;

    /**
     * LogConnectWorkspaceSerializer constructor.
     *
     * @param SerializerProvider $serializer
     */
    public function __construct(SerializerProvider $serializer)
    {
        $this->serializer = $serializer;
    }

    public function getClass()
    {
        return LogConnectWorkspace::class;
    }

    public function getName()
    {
        return 'log_connect_workspace';
    }

    /**
     * @param LogConnectWorkspace $log
     * @param array               $options
     *
     * @return array
     */
    public function serialize(LogConnectWorkspace $log, array $options = [])
    {
        $serialized = [
            'id' => $log->getUuid(),
            'date' => $log->getConnectionDate()->format('Y-m-d\TH:i:s'),
            'duration' => $log->getDuration(),
            'user' => $this->serializer->serialize($log->getUser(), [Options::SERIALIZE_MINIMAL]),
            'workspace' => $this->serializer->serialize($log->getWorkspace(), [Options::SERIALIZE_MINIMAL]),
            'workspaceName' => $log->getWorkspaceName(),
        ];

        return $serialized;
    }
}
