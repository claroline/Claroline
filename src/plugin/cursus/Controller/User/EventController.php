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
    public function __construct(
        private readonly AuthorizationCheckerInterface $authorization,
        private readonly TokenStorageInterface $tokenStorage,
        private readonly FinderProvider $finder
    ) {
    }

    /**
     * List the active (in progress and forthcoming) session events of the current user.
     *
     * @Route("/{workspace}", name="apiv2_cursus_my_events", methods={"GET"})
     *
     * @EXT\ParamConverter("workspace", class="Claroline\CoreBundle\Entity\Workspace\Workspace", options={"mapping": {"workspace": "uuid"}})
     */
    public function listAction(Request $request, Workspace $workspace = null): JsonResponse
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
}
