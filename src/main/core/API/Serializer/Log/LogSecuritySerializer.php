<?php

namespace Claroline\CoreBundle\API\Serializer\Log;

use Claroline\AppBundle\API\Options;
use Claroline\AppBundle\API\Serializer\SerializerTrait;
use Claroline\CoreBundle\API\Serializer\User\UserSerializer;
use Claroline\CoreBundle\Entity\Log\LogSecurity;
use Claroline\CoreBundle\Library\Normalizer\DateNormalizer;

class LogSecuritySerializer
{
    use SerializerTrait;

    private $userSerializer;

    public function __construct(UserSerializer $userSerializer)
    {
        $this->userSerializer = $userSerializer;
    }

    public function serialize(LogSecurity $logSecurity): array
    {
        $doer = null;
        if ($logSecurity->getDoer()) {
            $doer = $this->userSerializer->serialize($logSecurity->getDoer(), [Options::SERIALIZE_MINIMAL]);
        }

        $target = null;
        if ($logSecurity->getTarget()) {
            $target = $this->userSerializer->serialize($logSecurity->getTarget(), [Options::SERIALIZE_MINIMAL]);
        }

        return [
            'id' => $logSecurity->getId(),
            'city' => $logSecurity->getCity(),
            'country' => $logSecurity->getCountry(),
            'date' => DateNormalizer::normalize($logSecurity->getDate()),
            'details' => $logSecurity->getDetails(),
            'doer' => $doer,
            'event' => $logSecurity->getEvent(),
            'doer_ip' => $logSecurity->getDoerIp(),
            'target' => $target,
        ];
    }
}
