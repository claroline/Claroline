<?php

namespace HeVinci\FavouriteBundle\Manager;

use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Entity\Resource\ResourceNode;
use Claroline\CoreBundle\Entity\User;
use HeVinci\FavouriteBundle\Entity\Favourite;
use HeVinci\FavouriteBundle\Repository\FavouriteRepository;
use JMS\DiExtraBundle\Annotation as DI;

/**
 * @DI\Service("hevinci.favourite.manager")
 */
class FavouriteManager
{
    /** @var ObjectManager */
    private $om;

    /** @var FavouriteRepository */
    protected $repo;

    /**
     * @DI\InjectParams({
     *     "om" = @DI\Inject("claroline.persistence.object_manager"),
     * })
     *
     * @param ObjectManager $om
     */
    public function __construct(ObjectManager $om)
    {
        $this->om = $om;
        $this->repo = $om->getRepository('HeVinciFavouriteBundle:Favourite');
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
        $favourites = $this->repo->findByUser($from);

        if (count($favourites) > 0) {
            foreach ($favourites as $favourite) {
                $favourite->setUser($to);
            }

            $this->om->flush();
        }

        return count($favourites);
    }

    /**
     * Creates or deletes (depending on the first resource) favourites for given user and list of resources.
     *
     * @param User  $user
     * @param array $resourceNodes
     */
    public function toggleFavourites(User $user, array $resourceNodes)
    {
        if (0 < count($resourceNodes)) {
            $firstFavourite = $this->repo->findOneBy(['user' => $user, 'resourceNode' => $resourceNodes[0]]);
            $mode = empty($firstFavourite) ? 'create' : 'delete';

            $this->om->startFlushSuite();

            switch ($mode) {
                case 'create':
                    foreach ($resourceNodes as $resourceNode) {
                        $this->createFavourite($user, $resourceNode);
                    }
                    break;
                case 'delete':
                    foreach ($resourceNodes as $resourceNode) {
                        $this->deleteFavourite($user, $resourceNode);
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
    public function createFavourite(User $user, ResourceNode $resourceNode)
    {
        $favourite = $this->repo->findOneBy(['user' => $user, 'resourceNode' => $resourceNode]);

        if (empty($favourite)) {
            $favourite = new Favourite();
            $favourite->setUser($user);
            $favourite->setResourceNode($resourceNode);
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
    public function deleteFavourite(User $user, ResourceNode $resourceNode)
    {
        $favourite = $this->repo->findOneBy(['user' => $user, 'resourceNode' => $resourceNode]);

        if (!empty($favourite)) {
            $this->om->remove($favourite);
            $this->om->flush();
        }
    }
}
