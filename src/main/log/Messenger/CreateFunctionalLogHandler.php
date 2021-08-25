<?php

namespace Claroline\LogBundle\Messenger;

use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\LogBundle\Entity\FunctionalLog;
use Claroline\LogBundle\Messenger\Message\CreateFunctionalLog;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;

class CreateFunctionalLogHandler implements MessageHandlerInterface
{
    /** @var ObjectManager */
    private $om;

    public function __construct(ObjectManager $om)
    {
        $this->om = $om;
    }

    public function __invoke(CreateFunctionalLog $createLog)
    {
        $logEntry = new FunctionalLog();

        $logEntry->setDate($createLog->getDate());
        $logEntry->setEvent($createLog->getAction());
        $logEntry->setDetails($createLog->getDetails());
        $logEntry->setUser($createLog->getDoer());
        $logEntry->setWorkspace($createLog->getWorkspace());
        $logEntry->setResource($createLog->getResourceNode());

        $this->om->persist($logEntry);
        $this->om->flush();
    }
}
