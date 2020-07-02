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
use Claroline\CursusBundle\Entity\CourseRegistrationQueue;

class CourseQueueSerializer
{
    use SerializerTrait;

    /** @var SerializerProvider */
    private $serializer;

    /**
     * CourseQueueSerializer constructor.
     *
     * @param SerializerProvider $serializer
     */
    public function __construct(SerializerProvider $serializer)
    {
        $this->serializer = $serializer;
    }

    /**
     * @param CourseRegistrationQueue $queue
     * @param array                   $options
     *
     * @return array
     */
    public function serialize(CourseRegistrationQueue $queue, array $options = [])
    {
        $serialized = [
            'id' => $queue->getUuid(),
            'course' => $this->serializer->serialize($queue->getCourse(), [Options::SERIALIZE_MINIMAL]),
            'user' => $this->serializer->serialize($queue->getUser(), [Options::SERIALIZE_MINIMAL]),
            'status' => $queue->getStatus(),
            'applicationDate' => DateNormalizer::normalize($queue->getApplicationDate()),
        ];

        return $serialized;
    }
}
