<?php

namespace Claroline\AnalyticsBundle\Manager;

use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Entity\Log\Connection\LogConnectResource;
use Claroline\CoreBundle\Entity\Resource\ResourceNode;
use Claroline\CoreBundle\Repository\Log\Connection\LogConnectResourceRepository;

class ResourceManager
{
    /** @var ObjectManager */
    private $om;

    /** @var LogConnectResourceRepository */
    private $logConnectResourceRepo;

    public function __construct(ObjectManager $om)
    {
        $this->om = $om;

        $this->logConnectResourceRepo = $om->getRepository(LogConnectResource::class);
    }

    public function count(ResourceNode $resourceNode)
    {
        return [
            //'resources' => $this->resourceRepo->countActiveResources([$workspace]),
            //'storage' => $this->workspaceManager->getUsedStorage($workspace),
            'connections' => [
                'count' => $this->logConnectResourceRepo->countConnections($resourceNode),
                'avgTime' => $this->logConnectResourceRepo->findAvgTime($resourceNode), // in seconds
            ],
            //'users' => count($this->userRepo->findByWorkspaces([$workspace])),
            //'roles' => count($this->roleRepo->findBy(['workspace' => $workspace])),
            //'groups' => count($this->groupRepo->findByWorkspace($workspace)),
        ];
    }
}
