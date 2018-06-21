<?php

namespace HeVinci\FavouriteBundle\Controller\API;

use Claroline\AppBundle\Annotations\ApiMeta;
use Claroline\AppBundle\Controller\AbstractCrudController;
use Claroline\CoreBundle\Entity\User;
use HeVinci\FavouriteBundle\Manager\FavouriteManager;
use JMS\DiExtraBundle\Annotation as DI;
use Sensio\Bundle\FrameworkExtraBundle\Configuration as EXT;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * @ApiMeta(
 *     class="HeVinci\FavouriteBundle\Entity\Favourite",
 *     ignore={"create", "update", "deleteBulk", "exist", "list", "copyBulk", "schema", "find", "get"}
 * )
 * @EXT\Route("/favourite")
 */
class FavouriteController extends AbstractCrudController
{
    protected $manager;

    /**
     * @DI\InjectParams({
     *     "manager" = @DI\Inject("hevinci.favourite.manager")
     * })
     *
     * @param FavouriteManager $manager
     */
    public function __construct(FavouriteManager $manager)
    {
        $this->manager = $manager;
    }

    public function getName()
    {
        return 'favourite';
    }

    /**
     * Creates or deletes favourite resources.
     *
     * @EXT\Route("resources/toggle", name="hevinci_favourite_toggle")
     * @EXT\Method("PUT")
     * @EXT\ParamConverter("user", converter="current_user", options={"allowAnonymous"=false})
     *
     * @param User    $user
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function favouriteToggleAction(User $user, Request $request)
    {
        $nodes = $this->decodeIdsString($request, 'Claroline\CoreBundle\Entity\Resource\ResourceNode');
        $this->manager->toggleFavourites($user, $nodes);

        return new JsonResponse(null, 204);
    }
}
