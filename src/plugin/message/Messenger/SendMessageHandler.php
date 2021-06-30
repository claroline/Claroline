<?php

namespace Claroline\MessageBundle\Messenger;

use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Entity\User;
use Claroline\LogBundle\Entity\MessageLog;
use Claroline\MessageBundle\Messenger\Message\SendMessage;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;

class SendMessageHandler implements MessageHandlerInterface
{
    private $em;
    private $objectManager;

    public function __construct(EntityManagerInterface $em, ObjectManager $objectManager)
    {
        $this->em = $em;
        $this->objectManager = $objectManager;
    }

    public function __invoke(SendMessage $message)
    {
        $receiver = $this->objectManager->getRepository(User::class)->find($message->getReceiverId());
        $sender = $this->objectManager->getRepository(User::class)->find($message->getSenderId());

        $logEntry = new MessageLog();
        $logEntry->setDetails($message->getMessage());
        $logEntry->setEvent($message->getEventName());
        $logEntry->setReceiver($receiver);
        $logEntry->setSender($sender);

        $this->em->persist($logEntry);

        $this->em->flush();
    }
}
