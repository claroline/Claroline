<?php

namespace Claroline\CoreBundle\API\Serializer\Log;

use Claroline\AppBundle\API\Options;
use Claroline\AppBundle\API\Serializer\SerializerTrait;
use Claroline\CoreBundle\API\Serializer\User\UserSerializer;
use Claroline\CoreBundle\Entity\Log\FunctionalLog;
use Claroline\CoreBundle\Library\Normalizer\DateNormalizer;

class FunctionalLogSerializer
{
    use SerializerTrait;

    private $userSerializer;

    public function __construct(UserSerializer $userSerializer)
    {
        $this->userSerializer = $userSerializer;
    }

    public function serialize(FunctionalLog $functionalLog): array
    {
        $user = null;
        if ($functionalLog->getUser()) {
            $user = $this->userSerializer->serialize($functionalLog->getUser(), [Options::SERIALIZE_MINIMAL]);
        }

        return [
            'user' => $user,
            'date' => DateNormalizer::normalize($functionalLog->getDate()),
            'details' => $functionalLog->getDetails(),
            'event' => $functionalLog->getEvent(),
        ];
    }
}
