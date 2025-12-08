<?php

namespace Claroline\HomeBundle\Controller;

use Claroline\AppBundle\API\Serializer\SerializerInterface;
use Claroline\AppBundle\API\SerializerProvider;
use Claroline\AppBundle\Controller\RequestDecoderTrait;
use Claroline\CoreBundle\Security\PermissionCheckerTrait;
use Claroline\HomeBundle\Entity\HomeTab;
use Claroline\HomeBundle\Manager\HomeManager;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

#[Route(path: '/home_tab', name: 'apiv2_home_tab_')]
class HomeTabController
{
    use RequestDecoderTrait;
    use PermissionCheckerTrait;

    public function __construct(
        AuthorizationCheckerInterface $authorization,
        private readonly SerializerProvider $serializer,
        private readonly HomeManager $manager
    ) {
        $this->authorization = $authorization;
    }

    public static function getName(): string
    {
        return 'home_tab';
    }

    public static function getClass(): string
    {
        return HomeTab::class;
    }

    public function getIgnore(): array
    {
        return ['create', 'update', 'list'];
    }

    #[Route(path: '/open/{id}', name: 'open', methods: ['GET'])]
    public function openAction(#[MapEntity(mapping: ['id' => 'uuid'])] HomeTab $homeTab): JsonResponse
    {
        $accessErrors = $this->manager->getRestrictionsErrors($homeTab);
        $isManager = $this->checkPermission('EDIT', $homeTab);
        if (empty($accessErrors) || $isManager) {
            return new JsonResponse([
                'managed' => $isManager,
                'homeTab' => $this->serializer->serialize($homeTab),
                // append access restrictions to the loaded node if any
                // to let the manager knows that other users cannot enter the resource
                'accessErrors' => $accessErrors,
            ]);
        }

        return new JsonResponse([
            'managed' => true,
            'homeTab' => $this->serializer->serialize($homeTab, [SerializerInterface::SERIALIZE_MINIMAL]),
            // append access restrictions to the loaded node if any
            // to let the manager knows that other users cannot enter the resource
            'accessErrors' => $accessErrors,
        ], 403);
    }

    /**
     * Submit access code.
     */
    #[Route(path: '/unlock/{id}', name: 'unlock', methods: ['POST'])]
    public function unlockAction(#[MapEntity(mapping: ['id' => 'uuid'])] HomeTab $homeTab, Request $request): JsonResponse
    {
        $this->manager->unlock($homeTab, $request);

        return new JsonResponse(null, 204);
    }
}
