<?php

namespace Claroline\LogBundle\Messenger\Security;

use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Entity\User;
use Claroline\LogBundle\Entity\SecurityLog;
use Claroline\LogBundle\Messenger\Security\Message\SecurityMessageInterface;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;

final class SecurityLogHandler implements MessageHandlerInterface
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

    public function __invoke(SecurityMessageInterface $message): void
    {
        $target = $this->objectManager->getRepository(User::class)->find($message->getTargetId());
        $doer = $this->objectManager->getRepository(User::class)->find($message->getDoerId());

        $logEntry = new SecurityLog();
        $logEntry->setDetails($message->getMessage());
        $logEntry->setEvent($message->getName());
        $logEntry->setTarget($target);
        $logEntry->setDoer($doer);

        $this->em->persist($logEntry);
        $this->em->flush();
    }
}
