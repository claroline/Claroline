<?php

namespace Claroline\CoreBundle\Controller\APINew\Resource;

use Claroline\AppBundle\API\FinderProvider;
use Claroline\CoreBundle\Entity\Resource\ResourceNode;
use Claroline\CoreBundle\Security\PermissionCheckerTrait;
use Claroline\LogBundle\Entity\FunctionalLog;
use Sensio\Bundle\FrameworkExtraBundle\Configuration as EXT;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

/**
 * @Route("/resource/{id}")
 *
 * @EXT\ParamConverter("resourceNode", class="Claroline\CoreBundle\Entity\Resource\ResourceNode", options={"mapping": {"id": "uuid"}})
 */
class ActivityController
{
    use PermissionCheckerTrait;

    public function __construct(
        AuthorizationCheckerInterface $authorization,
        private readonly FinderProvider $finder
    ) {
        $this->authorization = $authorization;
    }

    /**
     * @Route("/logs", name="apiv2_resource_functional_logs")
     */
    public function functionalLogsAction(ResourceNode $resourceNode, Request $request): JsonResponse
    {
        $this->checkPermission($resourceNode, 'ADMINISTRATE', [], true);

        return new JsonResponse(
            $this->finder->search(FunctionalLog::class, array_merge($request->query->all(), [
                'hiddenFilters' => ['resource' => $resourceNode->getUuid()],
            ]))
        );
    }
}
