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
use JMS\DiExtraBundle\Annotation as DI;

/**
 * @DI\Service("claroline.manager.connection_message_manager")
 */
class ConnectionMessageManager
{
    /** @var SerializerProvider */
    private $serializer;

    /** @var ConnectionMessageRepository */
    private $connectionMessageRepo;

    /**
     * @DI\InjectParams({
     *     "om"         = @DI\Inject("claroline.persistence.object_manager"),
     *     "serializer" = @DI\Inject("claroline.api.serializer")
     * })
     *
     * @param ObjectManager      $om
     * @param SerializerProvider $serializer
     */
    public function __construct(ObjectManager $om, SerializerProvider $serializer)
    {
        $this->serializer = $serializer;

        $this->connectionMessageRepo = $om->getRepository(ConnectionMessage::class);
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
