<?php

namespace Icap\NotificationBundle\Serializer;

use Claroline\AppBundle\API\Options;
use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\API\Serializer\User\UserSerializer;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Library\Normalizer\DateNormalizer;
use Icap\NotificationBundle\Entity\Notification;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class NotificationSerializer
{
    /** @var ObjectManager */
    private $om;

    /** @var EventDispatcherInterface */
    private $eventDispatcher;

    /** @var UserSerializer */
    private $userSerializer;

    public function __construct(
        ObjectManager $om,
        EventDispatcherInterface $eventDispatcher,
        UserSerializer $userSerializer
    ) {
        $this->om = $om;
        $this->eventDispatcher = $eventDispatcher;
        $this->userSerializer = $userSerializer;
    }

    public function getClass()
    {
        return Notification::class;
    }

    public function getName()
    {
        return 'notification';
    }

    public function serialize(Notification $notification): array
    {
        $user = null;
        if (!empty($notification->getUserId())) {
            /** @var User $user */
            $user = $this->om->getRepository(User::class)->find($notification->getUserId());
        }

        return [
            'id' => $notification->getId(),

            'meta' => [
                'creator' => !empty($user) ? $this->userSerializer->serialize($user, [Options::SERIALIZE_MINIMAL]) : null,
                'created' => DateNormalizer::normalize($notification->getCreationDate()),
            ],

            'action' => $notification->getActionKey(),
            'details' => $notification->getDetails(),
        ];
    }
}
