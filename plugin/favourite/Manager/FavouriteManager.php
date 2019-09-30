<?php

namespace HeVinci\FavouriteBundle\Manager;

use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Entity\Resource\ResourceNode;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Entity\Workspace\Workspace;
use HeVinci\FavouriteBundle\Entity\ResourceFavourite;
use HeVinci\FavouriteBundle\Entity\WorkspaceFavourite;
use JMS\DiExtraBundle\Annotation as DI;

/**
 * @DI\Service("hevinci.favourite.manager")
 */
class FavouriteManager
{
    /** @var ObjectManager */
    private $om;

    /**
     * FavouriteManager constructor.
     *
     * @DI\InjectParams({
     *     "om" = @DI\Inject("claroline.persistence.object_manager"),
     * })
     *
     * @param ObjectManager $om
     */
    public function __construct(ObjectManager $om)
    {
        $this->om = $om;
    }

    /**
     * Get the list of favorited workspaces for a user.
     *
     * @param User $user
     *
     * @return Workspace[]
     */
    public function getWorkspaces(User $user)
    {
        $workspaces = $this->om
            ->getRepository(WorkspaceFavourite::class)
            ->findBy(['user' => $user]);

        return array_map(function (WorkspaceFavourite $favourite) {
            return $favourite->getWorkspace();
        }, $workspaces);
    }

    /**
     * Get the list of favorited resources for a user.
     *
     * @param User $user
     *
     * @return ResourceNode[]
     */
    public function getResources(User $user)
    {
        $resources = $this->om
            ->getRepository(ResourceFavourite::class)
            ->findBy(['user' => $user]);

        return array_map(function (ResourceFavourite $favourite) {
            return $favourite->getResource();
        }, $resources);
    }

    /**
     * Find all content for a given user and the replace him by another.
     *
     * @param User $from
     * @param User $to
     *
     * @return int
     */
    public function replaceUser(User $from, User $to)
    {
        /** @var WorkspaceFavourite[] $workspaceFavourites */
        $workspaceFavourites = $this->om
            ->getRepository(WorkspaceFavourite::class)
            ->findBy(['user' => $from]);

        /** @var ResourceFavourite[] $resourceFavourites */
        $resourceFavourites = $this->om
            ->getRepository(ResourceFavourite::class)
            ->findBy(['user' => $from]);

        $this->om->startFlushSuite();
        if (!empty($workspaceFavourites)) {
            foreach ($workspaceFavourites as $favourite) {
                $favourite->setUser($to);
            }
            $this->om->flush();
        }

        if (!empty($resourceFavourites)) {
            foreach ($resourceFavourites as $favourite) {
                $favourite->setUser($to);
            }
            $this->om->flush();
        }
        $this->om->endFlushSuite();

        return count($workspaceFavourites) + count($resourceFavourites);
    }

    /**
     * Creates or deletes (depending on the first resource) favourites for given user and list of resources.
     *
     * @param User           $user
     * @param ResourceNode[] $resourceNodes
     */
    public function toggleResourceFavourites(User $user, array $resourceNodes)
    {
        if (!empty($resourceNodes)) {
            $firstFavourite = $this->om
                ->getRepository(ResourceFavourite::class)
                ->findOneBy(['user' => $user, 'resource' => $resourceNodes[0]]);
            $mode = empty($firstFavourite) ? 'create' : 'delete';

            $this->om->startFlushSuite();
            switch ($mode) {
                case 'create':
                    foreach ($resourceNodes as $resourceNode) {
                        $this->createResourceFavourite($user, $resourceNode);
                    }
                    break;
                case 'delete':
                    foreach ($resourceNodes as $resourceNode) {
                        $this->deleteResourceFavourite($user, $resourceNode);
                    }
                    break;
            }
            $this->om->endFlushSuite();
        }
    }

    /**
     * Creates or deletes (depending on the first resource) favourites for given user and list of workspaces.
     *
     * @param User        $user
     * @param Workspace[] $workspaces
     */
    public function toggleWorkspaceFavourites(User $user, array $workspaces)
    {
        if (!empty($workspaces)) {
            $firstFavourite = $this->om
                ->getRepository(WorkspaceFavourite::class)
                ->findOneBy(['user' => $user, 'workspace' => $workspaces[0]]);
            $mode = empty($firstFavourite) ? 'create' : 'delete';

            $this->om->startFlushSuite();
            switch ($mode) {
                case 'create':
                    foreach ($workspaces as $workspace) {
                        $this->createWorkspaceFavourite($user, $workspace);
                    }
                    break;
                case 'delete':
                    foreach ($workspaces as $workspace) {
                        $this->deleteWorkspaceFavourite($user, $workspace);
                    }
                    break;
            }
            $this->om->endFlushSuite();
        }
    }

    /**
     * Creates a favourite for given user and resource.
     *
     * @param User         $user
     * @param ResourceNode $resourceNode
     */
    public function createResourceFavourite(User $user, ResourceNode $resourceNode)
    {
        $favourite = $this->om->getRepository(ResourceFavourite::class)->findOneBy([
            'user' => $user,
            'resource' => $resourceNode,
        ]);

        if (empty($favourite)) {
            $favourite = new ResourceFavourite();
            $favourite->setUser($user);
            $favourite->setResource($resourceNode);

            $this->om->persist($favourite);
            $this->om->flush();
        }
    }

    /**
     * Deletes favourite for given user and resource.
     *
     * @param User         $user
     * @param ResourceNode $resourceNode
     */
    public function deleteResourceFavourite(User $user, ResourceNode $resourceNode)
    {
        $favourite = $this->om->getRepository(ResourceFavourite::class)->findOneBy([
            'user' => $user,
            'resource' => $resourceNode,
        ]);

        if (!empty($favourite)) {
            $this->om->remove($favourite);
            $this->om->flush();
        }
    }

    /**
     * Creates a favourite for given user and workspace.
     *
     * @param User      $user
     * @param Workspace $workspace
     */
    public function createWorkspaceFavourite(User $user, Workspace $workspace)
    {
        $favourite = $this->om->getRepository(WorkspaceFavourite::class)->findOneBy([
            'user' => $user,
            'workspace' => $workspace,
        ]);

        if (empty($favourite)) {
            $favourite = new WorkspaceFavourite();
            $favourite->setUser($user);
            $favourite->setWorkspace($workspace);

            $this->om->persist($favourite);
            $this->om->flush();
        }
    }

    /**
     * Deletes favourite for given user and workspace.
     *
     * @param User      $user
     * @param Workspace $workspace
     */
    public function deleteWorkspaceFavourite(User $user, Workspace $workspace)
    {
        $favourite = $this->om->getRepository(WorkspaceFavourite::class)->findOneBy([
            'user' => $user,
            'workspace' => $workspace,
        ]);

        if (!empty($favourite)) {
            $this->om->remove($favourite);
            $this->om->flush();
        }
    }
}
