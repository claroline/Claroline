<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Manager;

use Claroline\AppBundle\API\SerializerProvider;
use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Entity\ConnectionMessage\ConnectionMessage;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Event\GenericDataEvent;
use Claroline\CoreBundle\Repository\ConnectionMessage\ConnectionMessageRepository;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class ConnectionMessageManager
{
    /** @var ObjectManager */
    private $om;

    /** @var EventDispatcherInterface */
    private $dispatcher;

    /** @var SerializerProvider */
    private $serializer;

    /** @var ConnectionMessageRepository */
    private $connectionMessageRepo;

    /**
     * ConnectionMessageManager constructor.
     *
     * @param ObjectManager            $om
     * @param EventDispatcherInterface $dispatcher
     * @param SerializerProvider       $serializer
     */
    public function __construct(
        ObjectManager $om,
        EventDispatcherInterface $dispatcher,
        SerializerProvider $serializer
    ) {
        $this->om = $om;
        $this->dispatcher = $dispatcher;
        $this->serializer = $serializer;

        $this->connectionMessageRepo = $om->getRepository(ConnectionMessage::class);
    }

    /**
     * Discards a message for a user.
     *
     * @param ConnectionMessage $message
     * @param User              $user
     */
    public function discard(ConnectionMessage $message, User $user)
    {
        $message->addUser($user);

        $this->om->persist($message);
        $this->om->flush();
    }

    /**
     * Retrieves list of connection messages for an user.
     *
     * @param User $user
     *
     * @return array
     */
    public function getConnectionMessagesByUser(User $user)
    {
        // get defined connection messages
        $messages = $this->connectionMessageRepo->findConnectionMessageByUser($user);
        $storedMessages = array_map(function (ConnectionMessage $message) {
            return $this->serializer->serialize($message);
        }, $messages);

        // grab connection messages from everywhere
        $event = new GenericDataEvent();
        $this->dispatcher->dispatch('platform.connection_messages.populate', $event);
        // TODO : find a way to validate populated data. For now I just expect an array which looks like
        // the return of ConnectionMessageSerializer
        $extMessages = $event->getData() ?? [];

        return array_merge($storedMessages, $extMessages);
    }
}
