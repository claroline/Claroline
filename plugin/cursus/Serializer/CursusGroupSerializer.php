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
use Claroline\CursusBundle\Entity\CursusGroup;

class CursusGroupSerializer
{
    use SerializerTrait;

    /** @var SerializerProvider */
    private $serializer;

    /**
     * CursusGroupSerializer constructor.
     *
     * @param SerializerProvider $serializer
     */
    public function __construct(SerializerProvider $serializer)
    {
        $this->serializer = $serializer;
    }

    /**
     * @param CursusGroup $cursusGroup
     * @param array       $options
     *
     * @return array
     */
    public function serialize(CursusGroup $cursusGroup, array $options = [])
    {
        $serialized = [
            'id' => $cursusGroup->getUuid(),
            'cursus' => $this->serializer->serialize($cursusGroup->getCursus(), [Options::SERIALIZE_MINIMAL]),
            'group' => $this->serializer->serialize($cursusGroup->getGroup(), [Options::SERIALIZE_MINIMAL]),
            'type' => $cursusGroup->getGroupType(),
            'registrationDate' => DateNormalizer::normalize($cursusGroup->getRegistrationDate()),
        ];

        return $serialized;
    }
}
