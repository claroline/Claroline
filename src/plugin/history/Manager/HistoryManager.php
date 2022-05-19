<?php

namespace Claroline\HistoryBundle\Manager;

use Claroline\AppBundle\Log\LoggableTrait;
use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Entity\Resource\ResourceNode;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Entity\Workspace\Workspace;
use Claroline\HistoryBundle\Entity\ResourceRecent;
use Claroline\HistoryBundle\Entity\WorkspaceRecent;
use Claroline\HistoryBundle\Repository\ResourceRecentRepository;
use Claroline\HistoryBundle\Repository\WorkspaceRecentRepository;
use Psr\Log\LoggerAwareInterface;

class HistoryManager implements LoggerAwareInterface
{
    use LoggableTrait;

    /**
     * The number of results to fetch when retrieving user history.
     */
    const HISTORY_RESULTS = 5;

    /** @var ObjectManager */
    private $om;

    /**
     * HistoryManager constructor.
     */
    public function __construct(ObjectManager $om)
    {
        $this->om = $om;
    }

    /**
     * Get the list of recent workspaces for a user.
     *
     * @return Workspace[]
     */
    public function getWorkspaces(User $user)
    {
        /** @var WorkspaceRecentRepository $repo */
        $repo = $this->om->getRepository(WorkspaceRecent::class);

        $workspaces = $repo->findEntries($user, static::HISTORY_RESULTS);

        return array_map(function (WorkspaceRecent $recent) {
            return $recent->getWorkspace();
        }, $workspaces);
    }

    /**
     * Get the list of recent resources for a user.
     *
     * @return ResourceNode[]
     */
    public function getResources(User $user)
    {
        /** @var ResourceRecentRepository $repo */
        $repo = $this->om->getRepository(ResourceRecent::class);

        $resources = $repo->findEntries($user, static::HISTORY_RESULTS);

        return array_map(function (ResourceRecent $recent) {
            return $recent->getResource();
        }, $resources);
    }

    /**
     * Add a workspace to the user history.
     */
    public function addWorkspace(Workspace $workspace, User $user)
    {
        // If object already in recent workspaces, update date
        $recentWorkspace = $this->om
            ->getRepository(WorkspaceRecent::class)
            ->findOneBy([
                'user' => $user,
                'workspace' => $workspace,
            ]);

        // Otherwise create new entry
        if (empty($recentWorkspace)) {
            $recentWorkspace = new WorkspaceRecent();
            $recentWorkspace->setUser($user);
            $recentWorkspace->setWorkspace($workspace);
        }
        $recentWorkspace->setCreatedAt(new \DateTime());

        $this->om->persist($recentWorkspace);
        $this->om->flush();
    }

    /**
     * Add a resource to the user history.
     */
    public function addResource(ResourceNode $resource, User $user)
    {
        // If object already in recent workspaces, update date
        $recentResource = $this->om
            ->getRepository(ResourceRecent::class)
            ->findOneBy([
                'user' => $user,
                'resource' => $resource,
            ]);

        // Otherwise create new entry
        if (empty($recentResource)) {
            $recentResource = new ResourceRecent();
            $recentResource->setUser($user);
            $recentResource->setResource($resource);
        }
        $recentResource->setCreatedAt(new \DateTime());

        $this->om->persist($recentResource);
        $this->om->flush();
    }

    /**
     * Clean all recent workspaces and resources that are more than 6 months old.
     */
    public function cleanRecent()
    {
        $this->log('Cleaning recent workspaces entries that are older than six months');

        /** @var WorkspaceRecentRepository $recentWorkspaceRepo */
        $recentWorkspaceRepo = $this->om->getRepository(WorkspaceRecent::class);
        $recentWorkspaceRepo->removeAllEntriesBefore(new \DateTime('-6 months'));

        $this->log('Cleaning recent resources entries that are older than six months');

        /** @var ResourceRecentRepository $recentResourceRepo */
        $recentResourceRepo = $this->om->getRepository(ResourceRecent::class);
        $recentResourceRepo->removeAllEntriesBefore(new \DateTime('-6 months'));
    }
}
