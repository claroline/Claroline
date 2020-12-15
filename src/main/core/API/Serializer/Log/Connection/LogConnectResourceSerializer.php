<?php

namespace Claroline\CoreBundle\API\Serializer\Log\Connection;

use Claroline\AppBundle\API\Options;
use Claroline\AppBundle\API\SerializerProvider;
use Claroline\CoreBundle\Entity\Log\Connection\LogConnectResource;

class LogConnectResourceSerializer
{
    /** @var SerializerProvider */
    private $serializer;

    /**
     * LogConnectResourceSerializer constructor.s.
     *
     * @param SerializerProvider $serializer
     */
    public function __construct(SerializerProvider $serializer)
    {
        $this->serializer = $serializer; // bad
    }

    public function getClass()
    {
        return LogConnectResource::class;
    }

    public function getName()
    {
        return 'log_connect_resource';
    }

    /**
     * @param LogConnectResource $log
     * @param array              $options
     *
     * @return array
     */
    public function serialize(LogConnectResource $log, array $options = [])
    {
        $serialized = [
            'id' => $log->getUuid(),
            'date' => $log->getConnectionDate()->format('Y-m-d\TH:i:s'),
            'duration' => $log->getDuration(),
            'user' => $this->serializer->serialize($log->getUser(), [Options::SERIALIZE_MINIMAL]),
            'resource' => $this->serializer->serialize($log->getResource(), [Options::SERIALIZE_MINIMAL]),
            'resourceName' => $log->getResourceName(),
            'resourceType' => $log->getResourceType(),
        ];

        return $serialized;
    }
}
