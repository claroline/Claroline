<?php

namespace HeVinci\FavouriteBundle\Manager;

use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Entity\User;
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
}
