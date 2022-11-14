<?php

namespace Claroline\LogBundle\Serializer;

use Claroline\AppBundle\API\Options;
use Claroline\AppBundle\API\Serializer\SerializerTrait;
use Claroline\CommunityBundle\Serializer\UserSerializer;
use Claroline\CoreBundle\Library\Normalizer\DateNormalizer;
use Claroline\LogBundle\Entity\SecurityLog;

class SecurityLogSerializer
{
    use SerializerTrait;

    private $userSerializer;

    public function __construct(UserSerializer $userSerializer)
    {
        $this->userSerializer = $userSerializer;
    }

    public function serialize(SecurityLog $securityLog): array
    {
        $doer = null;
        if ($securityLog->getDoer()) {
            $doer = $this->userSerializer->serialize($securityLog->getDoer(), [Options::SERIALIZE_MINIMAL]);
        }

        $target = null;
        if ($securityLog->getTarget()) {
            $target = $this->userSerializer->serialize($securityLog->getTarget(), [Options::SERIALIZE_MINIMAL]);
        }

        return [
            'city' => $securityLog->getCity(),
            'country' => $securityLog->getCountry(),
            'date' => DateNormalizer::normalize($securityLog->getDate()),
            'details' => $securityLog->getDetails(),
            'doer' => $doer,
            'event' => $securityLog->getEvent(),
            'doer_ip' => $securityLog->getDoerIp(),
            'target' => $target,
        ];
    }
}
