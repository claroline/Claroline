<?php

namespace Claroline\LogBundle\Serializer;

use Claroline\AppBundle\API\Serializer\SerializerInterface;
use Claroline\AppBundle\API\Serializer\SerializerTrait;
use Claroline\CommunityBundle\Serializer\UserSerializer;
use Claroline\CoreBundle\Library\Normalizer\DateNormalizer;
use Claroline\LogBundle\Entity\AbstractLog;

abstract class AbstractLogSerializer
{
    use SerializerTrait;

    public function __construct(
        protected readonly UserSerializer $userSerializer
    ) {
    }

    protected function serializeCommon(AbstractLog $log, array $options = []): array
    {
        if (in_array(SerializerInterface::SERIALIZE_MINIMAL, $options)) {
            return [
                'date' => DateNormalizer::normalize($log->getDate()),
                'event' => $log->getEvent(),
                'details' => $log->getDetails(),
            ];
        }

        $doer = null;
        if ($log->getDoer()) {
            $doer = $this->userSerializer->serialize($log->getDoer(), [SerializerInterface::SERIALIZE_MINIMAL]);
        }

        return [
            'date' => DateNormalizer::normalize($log->getDate()),
            'event' => $log->getEvent(),
            'details' => $log->getDetails(),

            // doer info
            'doer' => $doer,
            'doer_ip' => $log->getDoerIp(),
            'doer_city' => $log->getDoerCity(),
            'doer_country' => $log->getDoerCountry(),
        ];
    }
}
