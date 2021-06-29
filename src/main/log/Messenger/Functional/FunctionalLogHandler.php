<?php

namespace Claroline\LogBundle\Messenger\Functional;

use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Entity\Resource\ResourceNode;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Entity\Workspace\Workspace;
use Claroline\LogBundle\Entity\FunctionalLog;
use Claroline\LogBundle\Messenger\Functional\Message\FunctionalMessageInterface;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;

final class FunctionalLogHandler implements MessageHandlerInterface
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

    public function __invoke(FunctionalMessageInterface $message): void
    {
        $user = $this->objectManager->getRepository(User::class)->find($message->getUserId());

        if ($user) {
            // only create log for authenticated users
            $logEntry = new FunctionalLog();

            $logEntry->setUser($user);
            $logEntry->setDetails($message->getMessage());
            $logEntry->setEvent($message->getEventName());

            if (method_exists($message, 'getResourceId')) {
                $resource = $this->objectManager->getRepository(ResourceNode::class)->find($message->getResourceId());
                $logEntry->setResource($resource);
            } elseif (method_exists($message, 'getWorkspaceId')) {
                $workspace = $this->objectManager->getRepository(Workspace::class)->find($message->getWorkspaceId());
                $logEntry->setWorkspace($workspace);
            }

            $this->em->persist($logEntry);
            $this->em->flush();
        }
    }
}
