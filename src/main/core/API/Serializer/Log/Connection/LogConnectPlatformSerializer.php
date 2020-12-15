<?php

namespace Claroline\CoreBundle\API\Serializer\Log\Connection;

use Claroline\AppBundle\API\Options;
use Claroline\AppBundle\API\SerializerProvider;
use Claroline\CoreBundle\Entity\Log\Connection\LogConnectPlatform;

class LogConnectPlatformSerializer
{
    /** @var SerializerProvider */
    private $serializer;

    /**
     * LogConnectPlatformSerializer constructor.
     *
     * @param SerializerProvider $serializer
     */
    public function __construct(SerializerProvider $serializer)
    {
        $this->serializer = $serializer; // bad
    }

    public function getClass()
    {
        return LogConnectPlatform::class;
    }

    public function getName()
    {
        return 'log_connect_platform';
    }

    /**
     * @param LogConnectPlatform $log
     * @param array              $options
     *
     * @return array
     */
    public function serialize(LogConnectPlatform $log, array $options = [])
    {
        $serialized = [
            'id' => $log->getUuid(),
            'date' => $log->getConnectionDate()->format('Y-m-d\TH:i:s'),
            'duration' => $log->getDuration(),
            'user' => $this->serializer->serialize($log->getUser(), [Options::SERIALIZE_MINIMAL]),
        ];

        return $serialized;
    }
}
