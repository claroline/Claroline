<?php

namespace Claroline\CoreBundle\API\Serializer\Log\Connection;

use Claroline\AppBundle\API\Options;
use Claroline\AppBundle\API\Serializer\SerializerTrait;
use Claroline\AppBundle\API\SerializerProvider;
use Claroline\CoreBundle\Entity\Log\Connection\LogConnectResource;
use JMS\DiExtraBundle\Annotation as DI;

/**
 * @DI\Service("claroline.serializer.log.connect.resource")
 * @DI\Tag("claroline.serializer")
 */
class LogConnectResourceSerializer
{
    use SerializerTrait;

    /** @var SerializerProvider */
    private $serializer;

    /**
     * LogConnectResourceSerializer constructor.
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
        return LogConnectResource::class;
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
