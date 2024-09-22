<?php

namespace Claroline\CoreBundle\Controller\Resource;

use Claroline\AppBundle\API\FinderProvider;
use Claroline\CoreBundle\Entity\Organization\Organization;
use Claroline\CoreBundle\Entity\Resource\ResourceNode;
use Claroline\CoreBundle\Security\PermissionCheckerTrait;
use Claroline\CoreBundle\Security\PlatformRoles;
use Claroline\LogBundle\Entity\FunctionalLog;
use Sensio\Bundle\FrameworkExtraBundle\Configuration as EXT;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

/**
 * @EXT\ParamConverter("resourceNode", class="Claroline\CoreBundle\Entity\Resource\ResourceNode", options={"mapping": {"id": "uuid"}})
 */
#[Route(path: '/resource/{id}')]
class ActivityController
{
    use PermissionCheckerTrait;

    public function __construct(
        AuthorizationCheckerInterface $authorization,
        private readonly TokenStorageInterface $tokenStorage,
        private readonly FinderProvider $finder
    ) {
        $this->authorization = $authorization;
    }

    #[Route(path: '/logs', name: 'apiv2_resource_functional_logs')]
    public function functionalLogsAction(ResourceNode $resourceNode, Request $request): JsonResponse
    {
        $this->checkPermission('ADMINISTRATE', $resourceNode, [], true);

        $hiddenFilters = [
            'resource' => $resourceNode->getUuid(),
        ];

        if (!$this->authorization->isGranted(PlatformRoles::ADMIN)) {
            $user = $this->tokenStorage->getToken()->getUser();

            $organizations = array_map(function (Organization $organization) {
                return $organization->getUuid();
            }, $user->getOrganizations());
            $hiddenFilters['organizations'] = $organizations;
        }

        return new JsonResponse(
            $this->finder->search(FunctionalLog::class, array_merge($request->query->all(), [
                'hiddenFilters' => $hiddenFilters,
            ]))
        );
    }
}
