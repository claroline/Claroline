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
use Claroline\CommunityBundle\Serializer\UserSerializer;
use Claroline\CursusBundle\Entity\Registration\AbstractUserRegistration;
use Claroline\CursusBundle\Entity\Registration\EventUser;
use Claroline\CursusBundle\Serializer\EventSerializer;

class EventUserSerializer extends AbstractUserSerializer
{
    use SerializerTrait;

    /** @var EventSerializer */
    private $eventSerializer;

    public function __construct(UserSerializer $userSerializer, EventSerializer $eventSerializer)
    {
        parent::__construct($userSerializer);

        $this->eventSerializer = $eventSerializer;
    }

    public function getClass()
    {
        return EventUser::class;
    }

    /**
     * @param EventUser $eventUser
     */
    public function serialize(AbstractUserRegistration $eventUser, array $options = []): array
    {
        return array_merge(parent::serialize($eventUser, $options), [
            'event' => $this->eventSerializer->serialize($eventUser->getEvent(), [Options::SERIALIZE_MINIMAL]),
        ]);
    }
}
