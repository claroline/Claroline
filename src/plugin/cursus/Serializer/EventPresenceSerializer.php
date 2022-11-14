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
use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CommunityBundle\Serializer\UserSerializer;
use Claroline\CoreBundle\Entity\User;
use Claroline\CursusBundle\Entity\EventPresence;

class EventPresenceSerializer
{
    use SerializerTrait;

    /** @var ObjectManager */
    private $om;
    /** @var UserSerializer */
    private $userSerializer;

    public function __construct(
        ObjectManager $om,
        UserSerializer $userSerializer
    ) {
        $this->om = $om;
        $this->userSerializer = $userSerializer;
    }

    public function serialize(EventPresence $eventPresence, array $options = []): array
    {
        return [
            'id' => $eventPresence->getUuid(),
            'user' => $this->userSerializer->serialize($eventPresence->getUser(), [Options::SERIALIZE_MINIMAL]),
            'status' => $eventPresence->getStatus(),
        ];
    }

    public function deserialize(array $data, EventPresence $eventPresence): EventPresence
    {
        $this->sipe('id', 'setUuid', $data, $eventPresence);
        $this->sipe('status', 'setStatus', $data, $eventPresence);

        if (isset($data['user'])) {
            $user = null;
            if (isset($data['user']['id'])) {
                $user = $this->om->getRepository(User::class)->findOneBy(['uuid' => $data['user']['id']]);
            }

            $eventPresence->setUser($user);
        }

        return $eventPresence;
    }
}
