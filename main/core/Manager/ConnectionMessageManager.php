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
use Claroline\CoreBundle\Repository\ConnectionMessage\ConnectionMessageRepository;

class ConnectionMessageManager
{
    /** @var ObjectManager */
    private $om;

    /** @var SerializerProvider */
    private $serializer;

    /** @var ConnectionMessageRepository */
    private $connectionMessageRepo;

    /**
     * @param ObjectManager      $om
     * @param SerializerProvider $serializer
     */
    public function __construct(ObjectManager $om, SerializerProvider $serializer)
    {
        $this->om = $om;
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
        $messages = $this->connectionMessageRepo->findConnectionMessageByUser($user);

        return array_map(function (ConnectionMessage $message) {
            return $this->serializer->serialize($message);
        }, $messages);
    }
}
