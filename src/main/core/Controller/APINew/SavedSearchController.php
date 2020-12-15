<?php

namespace Claroline\CoreBundle\Controller\APINew;

use Claroline\AppBundle\API\Crud;
use Claroline\AppBundle\API\FinderProvider;
use Claroline\AppBundle\API\SerializerProvider;
use Claroline\AppBundle\Controller\AbstractApiController;
use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Entity\SavedSearch;
use Claroline\CoreBundle\Entity\User;
use Sensio\Bundle\FrameworkExtraBundle\Configuration as EXT;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Exposes user saved searches.
 *
 * @Route("/saved_search", options={"expose": true})
 * @EXT\ParamConverter("currentUser", converter="current_user", options={"allowAnonymous"=false})
 */
class SavedSearchController extends AbstractApiController
{
    /** @var ObjectManager */
    private $om;
    /** @var SerializerProvider */
    private $serializer;
    /** @var FinderProvider */
    private $finder;
    /** @var Crud */
    private $crud;

    /**
     * SavedSearchController constructor.
     *
     * @param ObjectManager      $om
     * @param SerializerProvider $serializer
     * @param FinderProvider     $finder
     * @param Crud               $crud
     */
    public function __construct(
        ObjectManager $om,
        SerializerProvider $serializer,
        FinderProvider $finder,
        Crud $crud)
    {
        $this->om = $om;
        $this->serializer = $serializer;
        $this->finder = $finder;
        $this->crud = $crud;
    }

    /**
     * Lists saved searches of the current user for a list.
     *
     * @Route("/{list}", name="apiv2_saved_search_list", methods={"GET"})
     *
     * @param User   $currentUser
     * @param string $list
     *
     * @return JsonResponse
     */
    public function listAction(User $currentUser, $list)
    {
        $savedSearches = $this->finder->fetch(SavedSearch::class, [
            'list' => $list,
            'user' => $currentUser,
        ], [
            'property' => 'name',
            'direction' => 1,
        ]);

        return new JsonResponse(array_map(function (SavedSearch $savedSearch) {
            return $this->serializer->serialize($savedSearch);
        }, $savedSearches));
    }

    /**
     * Creates a searches for the current user and a list.
     *
     * @Route("/{list}", name="apiv2_saved_search_create", methods={"GET"})
     *
     * @param User    $currentUser
     * @param string  $list
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function createAction(User $currentUser, $list, Request $request)
    {
        $this->om->startFlushSuite();

        /** @var SavedSearch $savedSearch */
        $savedSearch = $this->crud->create(SavedSearch::class, $this->decodeRequest($request), [Crud::THROW_EXCEPTION]);
        $savedSearch->setUser($currentUser);

        $this->om->endFlushSuite();

        return new JsonResponse($savedSearch, 201);
    }
}
