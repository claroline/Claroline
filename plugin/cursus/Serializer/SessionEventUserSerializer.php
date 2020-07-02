<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CursusBundle\Serializer;

use Claroline\AppBundle\API\Options;
use Claroline\AppBundle\API\Serializer\SerializerTrait;
use Claroline\AppBundle\API\SerializerProvider;
use Claroline\CoreBundle\Library\Normalizer\DateNormalizer;
use Claroline\CursusBundle\Entity\SessionEventUser;

class SessionEventUserSerializer
{
    use SerializerTrait;

    /** @var SerializerProvider */
    private $serializer;

    /**
     * SessionEventUserSerializer constructor.
     *
     * @param SerializerProvider $serializer
     */
    public function __construct(SerializerProvider $serializer)
    {
        $this->serializer = $serializer;
    }

    /**
     * @param SessionEventUser $sessionEventUser
     * @param array            $options
     *
     * @return array
     */
    public function serialize(SessionEventUser $sessionEventUser, array $options = [])
    {
        $serialized = [
            'id' => $sessionEventUser->getUuid(),
            'sessionEvent' => $this->serializer->serialize($sessionEventUser->getSessionEvent(), [Options::SERIALIZE_MINIMAL]),
            'user' => $this->serializer->serialize($sessionEventUser->getUser(), [Options::SERIALIZE_MINIMAL]),
            'status' => $sessionEventUser->getRegistrationStatus(),
            'registrationDate' => DateNormalizer::normalize($sessionEventUser->getRegistrationDate()),
            'applicationDate' => DateNormalizer::normalize($sessionEventUser->getApplicationDate()),
        ];

        return $serialized;
    }
}
