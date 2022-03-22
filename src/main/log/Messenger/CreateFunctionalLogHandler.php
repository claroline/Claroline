<?php

namespace Claroline\LogBundle\Messenger;

use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Entity\Resource\ResourceNode;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Entity\Workspace\Workspace;
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
        $doer = $this->om->getRepository(User::class)->find($createLog->getDoerId());
        if (empty($doer)) {
            // missing data, no need to log things
            return;
        }

        $logEntry = new FunctionalLog();

        $logEntry->setDate($createLog->getDate());
        $logEntry->setEvent($createLog->getAction());
        $logEntry->setDetails($createLog->getDetails());
        $logEntry->setUser($doer);

        if ($createLog->getWorkspaceId()) {
            $workspace = $this->om->getRepository(Workspace::class)->find($createLog->getWorkspaceId());
            $logEntry->setWorkspace($workspace);
        }

        if ($createLog->getResourceNodeId()) {
            $resourceNode = $this->om->getRepository(ResourceNode::class)->find($createLog->getResourceNodeId());
            $logEntry->setResource($resourceNode);
        }

        $this->om->persist($logEntry);
        $this->om->flush();
    }
}
