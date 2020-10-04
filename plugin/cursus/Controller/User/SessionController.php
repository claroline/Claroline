<?php

namespace Claroline\CursusBundle\Controller\User;

use Claroline\AppBundle\API\FinderProvider;
use Claroline\CursusBundle\Entity\Session;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

/**
 * Exposes API for the sessions of the current user.
 *
 * @Route("/my_sessions")
 */
class SessionController
{
    /** @var AuthorizationCheckerInterface */
    private $authorization;
    /** @var TokenStorageInterface */
    private $tokenStorage;
    /** @var FinderProvider */
    private $finder;

    public function __construct(
        AuthorizationCheckerInterface $authorization,
        TokenStorageInterface $tokenStorage,
        FinderProvider $finder)
    {
        $this->authorization = $authorization;
        $this->tokenStorage = $tokenStorage;
        $this->finder = $finder;
    }

    /**
     * List the active (in progress and forthcoming) sessions of the current user.
     *
     * @Route("/active", name="apiv2_cursus_my_sessions_active", methods={"GET"})
     */
    public function listActiveAction(Request $request): JsonResponse
    {
        if (!$this->authorization->isGranted('IS_AUTHENTICATED_FULLY')) {
            throw new AccessDeniedException();
        }

        $params = $request->query->all();
        $params['hiddenFilters'] = [];
        $params['hiddenFilters']['user'] = $this->tokenStorage->getToken()->getUser()->getUuid();
        $params['hiddenFilters']['terminated'] = false;

        return new JsonResponse(
            $this->finder->search(Session::class, $params)
        );
    }

    /**
     * List the ended sessions of the current user.
     *
     * @Route("/ended", name="apiv2_cursus_my_sessions_ended", methods={"GET"})
     */
    public function listEndedAction(Request $request): JsonResponse
    {
        if (!$this->authorization->isGranted('IS_AUTHENTICATED_FULLY')) {
            throw new AccessDeniedException();
        }

        $params = $request->query->all();
        $params['hiddenFilters'] = [];
        $params['hiddenFilters']['user'] = $this->tokenStorage->getToken()->getUser()->getUuid();
        $params['hiddenFilters']['terminated'] = true;

        return new JsonResponse(
            $this->finder->search(Session::class, $params)
        );
    }

    /**
     * List the sessions for which the user is in pending list.
     *
     * @Route("/", name="apiv2_cursus_my_sessions_pending", methods={"GET"})
     */
    public function listPendingAction(Request $request): JsonResponse
    {
        if (!$this->authorization->isGranted('IS_AUTHENTICATED_FULLY')) {
            throw new AccessDeniedException();
        }

        $params = $request->query->all();
        $params['hiddenFilters'] = [];
        $params['hiddenFilters']['userPending'] = $this->tokenStorage->getToken()->getUser()->getUuid();

        return new JsonResponse(
            $this->finder->search(Session::class, $params)
        );
    }
}
