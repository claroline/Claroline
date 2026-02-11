<?php

namespace Claroline\HomeBundle\Controller;

use Claroline\AppBundle\API\Crud;
use Claroline\AppBundle\API\Finder\FinderRequest;
use Claroline\AppBundle\API\Serializer\SerializerInterface;
use Claroline\AppBundle\API\SerializerProvider;
use Claroline\AppBundle\Controller\RequestDecoderTrait;
use Claroline\AppBundle\Manager\ViewerManager;
use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Security\PermissionCheckerTrait;
use Claroline\CoreBundle\Security\PlatformRoles;
use Claroline\HomeBundle\Entity\HomeTab;
use Claroline\HomeBundle\Entity\HomeTabView;
use Claroline\HomeBundle\Finder\HomeTabViewType;
use Claroline\HomeBundle\Manager\HomeRestrictionsManager;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\StreamedJsonResponse;
use Symfony\Component\HttpKernel\Attribute\MapQueryString;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Http\Attribute\CurrentUser;

#[Route(path: '/home_tab', name: 'apiv2_home_tab_')]
class HomeTabController
{
    use RequestDecoderTrait;
    use PermissionCheckerTrait;

    public function __construct(
        AuthorizationCheckerInterface $authorization,
        private readonly TokenStorageInterface $tokenStorage,
        private readonly SerializerProvider $serializer,
        private readonly ObjectManager $om,
        private readonly Crud $crud,
        private readonly HomeRestrictionsManager $restrictionsManager,
        private readonly ViewerManager $viewerManager
    ) {
        $this->authorization = $authorization;
    }

    #[Route(path: '/open/{id}', name: 'open', methods: ['GET'])]
    public function openAction(
        #[MapEntity(mapping: ['id' => 'uuid'])]
        HomeTab $homeTab
    ): JsonResponse {
        if ($this->authorization->isGranted('OPEN', $homeTab)) {
            return new JsonResponse([
                'homeTab' => $this->serializer->serialize($homeTab),
            ]);
        }

        // return the details of access errors to display it to users
        $userRoles = $this->tokenStorage->getToken()?->getRoleNames() ?? [PlatformRoles::ANONYMOUS];
        $accessError = $this->restrictionsManager->getError($homeTab, $userRoles);

        return new JsonResponse([
            'homeTab' => $this->serializer->serialize($homeTab, [SerializerInterface::SERIALIZE_MINIMAL]),
            'error' => $accessError,
        ], 403);
    }

    /**
     * Submit access code.
     */
    #[Route(path: '/unlock/{id}', name: 'unlock', methods: ['POST'])]
    public function unlockAction(#[MapEntity(mapping: ['id' => 'uuid'])] HomeTab $homeTab, Request $request): JsonResponse
    {
        $this->restrictionsManager->unlock($homeTab, $request);

        return new JsonResponse(null, 204);
    }

    #[Route(path: '/{id}/view', name: 'view_update', methods: ['PUT'])]
    public function addViewAction(
        #[CurrentUser]
        ?User $user,
        #[MapEntity(mapping: ['id' => 'uuid'])]
        HomeTab $homeTab,
    ): JsonResponse {
        $this->checkPermission('OPEN', $homeTab, [], true);

        if (null === $user) {
            return new JsonResponse(null, 204);
        }
        $this->viewerManager->addView(HomeTabView::class, $homeTab, $user);

        return new JsonResponse([
            'nbViews' => $homeTab->getViews(),
        ]);
    }

    #[Route(path: '/{id}/activity/{activityType<(views|visitors)>}', name: 'activity', methods: ['GET'])]
    public function activityAction(
        #[MapEntity(mapping: ['id' => 'uuid'])]
        HomeTab $homeTab,
        string $activityType
    ): JsonResponse {
        $this->checkPermission('FOLLOW', $homeTab, [], true);

        switch ($activityType) {
            case 'views':
                $activity = $this->om->getRepository(HomeTabView::class)->findViewsForPeriod(
                    $homeTab,
                    $this->tokenStorage->getToken()->getUser()->getMainOrganization()
                );
                break;

            case 'visitors':
                $activity = $this->om->getRepository(HomeTabView::class)->findVisitorsForPeriod(
                    $homeTab,
                    $this->tokenStorage->getToken()->getUser()->getMainOrganization()
                );
                break;
        }

        return new JsonResponse($activity);
    }

    #[Route(path: '/{id}/views', name: 'views', methods: ['GET'])]
    public function viewsAction(
        #[MapEntity(mapping: ['id' => 'uuid'])]
        HomeTab $homeTab,
        #[MapQueryString]
        ?FinderRequest $finderRequest = new FinderRequest()
    ): StreamedJsonResponse {
        $this->checkPermission('FOLLOW', $homeTab, [], true);

        $finderRequest->addFilter('homeTab', $homeTab->getUuid());

        $viewers = $this->viewerManager->listViews(HomeTabViewType::class, $finderRequest);

        return $viewers->toResponse();
    }
}
