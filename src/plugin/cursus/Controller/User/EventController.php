<?php

namespace Claroline\CursusBundle\Controller\User;

use Claroline\AppBundle\API\FinderProvider;
use Claroline\CoreBundle\Entity\Workspace\Workspace;
use Claroline\CursusBundle\Entity\Event;
use Sensio\Bundle\FrameworkExtraBundle\Configuration as EXT;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

/**
 * Exposes API for the session events of the current user.
 *
 * @Route("/my_events")
 */
class EventController
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
     * List the active (in progress and forthcoming) session events of the current user.
     *
     * @Route("/active/{workspace}", name="apiv2_cursus_my_events_active", methods={"GET"})
     * @EXT\ParamConverter("workspace", class="Claroline\CoreBundle\Entity\Workspace\Workspace", options={"mapping": {"workspace": "uuid"}})
     */
    public function listActiveAction(Request $request, Workspace $workspace = null): JsonResponse
    {
        if (!$this->authorization->isGranted('IS_AUTHENTICATED_FULLY')) {
            throw new AccessDeniedException();
        }

        $params = $request->query->all();
        $params['hiddenFilters'] = [];
        $params['hiddenFilters']['user'] = $this->tokenStorage->getToken()->getUser()->getUuid();
        $params['hiddenFilters']['terminated'] = false;
        if ($workspace) {
            $params['hiddenFilters']['workspace'] = $workspace->getUuid();
        }

        return new JsonResponse(
            $this->finder->search(Event::class, $params)
        );
    }

    /**
     * List the ended session events of the current user.
     *
     * @Route("/ended/{workspace}", name="apiv2_cursus_my_events_ended", methods={"GET"})
     * @EXT\ParamConverter("workspace", class="Claroline\CoreBundle\Entity\Workspace\Workspace", options={"mapping": {"workspace": "uuid"}})
     */
    public function listEndedAction(Request $request, Workspace $workspace = null): JsonResponse
    {
        if (!$this->authorization->isGranted('IS_AUTHENTICATED_FULLY')) {
            throw new AccessDeniedException();
        }

        $params = $request->query->all();
        $params['hiddenFilters'] = [];
        $params['hiddenFilters']['user'] = $this->tokenStorage->getToken()->getUser()->getUuid();
        $params['hiddenFilters']['terminated'] = true;
        if ($workspace) {
            $params['hiddenFilters']['workspace'] = $workspace->getUuid();
        }

        return new JsonResponse(
            $this->finder->search(Event::class, $params)
        );
    }
}
