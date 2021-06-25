<?php

namespace Claroline\LogBundle\Messenger\Security;

use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Entity\User;
use Claroline\LogBundle\Entity\SecurityLog;
use Claroline\LogBundle\Messenger\Security\Message\SecurityMessageInterface;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;

final class SecurityLogHandler implements MessageHandlerInterface
{
    private $em;
    private $objectManager;
    private $requestStack;

    public function __construct(
        EntityManagerInterface $em,
        ObjectManager $objectManager,
        RequestStack $requestStack
    ) {
        $this->em = $em;
        $this->objectManager = $objectManager;
        $this->requestStack = $requestStack;
    }

    public function __invoke(SecurityMessageInterface $message): void
    {
        $target = $this->objectManager->getRepository(User::class)->find($message->getTargetId());
        $doer = $this->objectManager->getRepository(User::class)->find($message->getDoerId());

        $logEntry = new SecurityLog();
        $logEntry->setDetails($message->getMessage());
        $logEntry->setEvent($message->getEventName());
        $logEntry->setTarget($target);
        $logEntry->setDoer($doer);
        $logEntry->setDoerIp($this->getDoerIp());

        $this->em->persist($logEntry);
        $this->em->flush();
    }

    private function getDoerIp(): string
    {
        $doerIp = 'CLI';

        $request = $this->requestStack->getCurrentRequest();
        if ($request) {
            $doerIp = $request->getClientIp();
        }

        return $doerIp;
    }
}
