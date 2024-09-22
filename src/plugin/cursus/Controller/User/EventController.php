<?php

namespace Claroline\CursusBundle\Controller\User;

use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Claroline\AppBundle\API\FinderProvider;
use Claroline\CoreBundle\Entity\Workspace\Workspace;
use Claroline\CursusBundle\Entity\Event;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

/**
 * Exposes API for the session events of the current user.
 */
#[Route(path: '/my_events')]
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
     */
    #[Route(path: '/{workspace}', name: 'apiv2_cursus_my_events', methods: ['GET'])]
    public function listAction(Request $request, #[MapEntity(class: 'Claroline\CoreBundle\Entity\Workspace\Workspace', mapping: ['workspace' => 'uuid'])]
    Workspace $workspace = null): JsonResponse
    {
        if (!$this->authorization->isGranted('IS_AUTHENTICATED_FULLY')) {
            throw new AccessDeniedException();
        }

        $params = $request->query->all();
        $params['hiddenFilters'] = [];
        $params['hiddenFilters']['user'] = $this->tokenStorage->getToken()?->getUser()->getUuid();
        $params['hiddenFilters']['terminated'] = false;
        if ($workspace) {
            $params['hiddenFilters']['workspace'] = $workspace->getUuid();
        }

        return new JsonResponse(
            $this->finder->search(Event::class, $params)
        );
    }
}
