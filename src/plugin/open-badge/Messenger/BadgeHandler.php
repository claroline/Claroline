<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\OpenBadgeBundle\Messenger;

use Claroline\AppBundle\Messenger\Message\BadgeMessageInterface;
use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Entity\User;
use Claroline\LogBundle\Entity\FunctionalLog;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;

class BadgeHandler implements MessageHandlerInterface
{
    private $em;
    private $objectManager;

    public function __construct(
        EntityManagerInterface $em,
        ObjectManager $objectManager
    ) {
        $this->em = $em;
        $this->objectManager = $objectManager;
    }

    public function __invoke(BadgeMessageInterface $message)
    {
        $user = $this->objectManager->getRepository(User::class)->find($message->getUserId());

        $logEntry = new FunctionalLog();

        $logEntry->setUser($user);
        $logEntry->setDetails($message->getMessage());
        $logEntry->setEvent($message->getEventName());

        $this->em->persist($logEntry);
        $this->em->flush();
    }
}
