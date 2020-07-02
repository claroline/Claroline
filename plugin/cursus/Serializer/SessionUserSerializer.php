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
use Claroline\CursusBundle\Entity\CourseSessionUser;

class SessionUserSerializer
{
    use SerializerTrait;

    /** @var SerializerProvider */
    private $serializer;

    /**
     * SessionUserSerializer constructor.
     *
     * @param SerializerProvider $serializer
     */
    public function __construct(SerializerProvider $serializer)
    {
        $this->serializer = $serializer;
    }

    /**
     * @param CourseSessionUser $sessionUser
     * @param array             $options
     *
     * @return array
     */
    public function serialize(CourseSessionUser $sessionUser, array $options = [])
    {
        $serialized = [
            'id' => $sessionUser->getUuid(),
            'session' => $this->serializer->serialize($sessionUser->getSession(), [Options::SERIALIZE_MINIMAL]),
            'user' => $this->serializer->serialize($sessionUser->getUser(), [Options::SERIALIZE_MINIMAL]),
            'type' => $sessionUser->getUserType(),
            'registrationDate' => DateNormalizer::normalize($sessionUser->getRegistrationDate()),
        ];

        return $serialized;
    }
}
