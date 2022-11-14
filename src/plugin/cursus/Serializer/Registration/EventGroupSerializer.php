<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CursusBundle\Serializer\Registration;

use Claroline\AppBundle\API\Options;
use Claroline\AppBundle\API\Serializer\SerializerTrait;
use Claroline\CommunityBundle\Serializer\GroupSerializer;
use Claroline\CursusBundle\Entity\Registration\AbstractGroupRegistration;
use Claroline\CursusBundle\Entity\Registration\EventGroup;
use Claroline\CursusBundle\Serializer\EventSerializer;

class EventGroupSerializer extends AbstractGroupSerializer
{
    use SerializerTrait;

    /** @var EventSerializer */
    private $eventSerializer;

    public function __construct(GroupSerializer $groupSerializer, EventSerializer $eventSerializer)
    {
        parent::__construct($groupSerializer);

        $this->eventSerializer = $eventSerializer;
    }

    public function getClass()
    {
        return EventGroup::class;
    }

    /**
     * @param EventGroup $eventGroup
     */
    public function serialize(AbstractGroupRegistration $eventGroup, array $options = []): array
    {
        return array_merge(parent::serialize($eventGroup, $options), [
            'event' => $this->eventSerializer->serialize($eventGroup->getEvent(), [Options::SERIALIZE_MINIMAL]),
        ]);
    }
}
