<?php

namespace Claroline\LogBundle\Messenger;

use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\LogBundle\Entity\SecurityLog;
use Claroline\LogBundle\Messenger\Message\CreateSecurityLog;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;

class CreateSecurityLogHandler implements MessageHandlerInterface
{
    /** @var ObjectManager */
    private $om;

    public function __construct(ObjectManager $om)
    {
        $this->om = $om;
    }

    public function __invoke(CreateSecurityLog $createLog)
    {
        $logEntry = new SecurityLog();

        $logEntry->setDate($createLog->getDate());
        $logEntry->setEvent($createLog->getAction());
        $logEntry->setDetails($createLog->getDetails());
        $logEntry->setDoer($createLog->getDoer());
        $logEntry->setTarget($createLog->getTarget());
        $logEntry->setDoerIp($createLog->getDoerIp());
        $logEntry->setCountry($createLog->getDoerCountry());
        $logEntry->setCity($createLog->getDoerCity());

        $this->om->persist($logEntry);
        $this->om->flush();
    }
}
