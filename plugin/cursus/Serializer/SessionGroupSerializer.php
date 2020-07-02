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
use Claroline\CursusBundle\Entity\CourseSessionGroup;

class SessionGroupSerializer
{
    use SerializerTrait;

    /** @var SerializerProvider */
    private $serializer;

    /**
     * SessionGroupSerializer constructor.
     *
     * @param SerializerProvider $serializer
     */
    public function __construct(SerializerProvider $serializer)
    {
        $this->serializer = $serializer;
    }

    /**
     * @param CourseSessionGroup $sessionGroup
     * @param array              $options
     *
     * @return array
     */
    public function serialize(CourseSessionGroup $sessionGroup, array $options = [])
    {
        $serialized = [
            'id' => $sessionGroup->getUuid(),
            'session' => $this->serializer->serialize($sessionGroup->getSession(), [Options::SERIALIZE_MINIMAL]),
            'group' => $this->serializer->serialize($sessionGroup->getGroup(), [Options::SERIALIZE_MINIMAL]),
            'type' => $sessionGroup->getGroupType(),
            'registrationDate' => DateNormalizer::normalize($sessionGroup->getRegistrationDate()),
        ];

        return $serialized;
    }
}
