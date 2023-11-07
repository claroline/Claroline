<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Controller\APINew\Log;

use Claroline\AppBundle\API\FinderProvider;
use Claroline\CoreBundle\Entity\Log\Connection\LogConnectPlatform;
use Claroline\CoreBundle\Entity\Log\Connection\LogConnectResource;
use Claroline\CoreBundle\Entity\Log\Connection\LogConnectWorkspace;
use Claroline\CoreBundle\Entity\Organization\Organization;
use Claroline\CoreBundle\Entity\Resource\ResourceNode;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Entity\Workspace\Workspace;
use Claroline\CoreBundle\Manager\LogConnectManager;
use Claroline\CoreBundle\Manager\Tool\ToolManager;
use Claroline\CoreBundle\Security\ToolPermissions;
use Sensio\Bundle\FrameworkExtraBundle\Configuration as EXT;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

/**
 * @Route("/log_connect")
 */
class LogConnectController
{
    /** @var AuthorizationCheckerInterface */
    private $authorization;

    /** @var FinderProvider */
    private $finder;

    /** @var LogConnectManager */
    private $logConnectManager;

    /** @var ToolManager */
    private $toolManager;

    /**
     * CourseController constructor.
     */
    public function __construct(
        AuthorizationCheckerInterface $authorization,
        FinderProvider $finder,
        LogConnectManager $logConnectManager,
        ToolManager $toolManager
    ) {
        $this->authorization = $authorization;
        $this->finder = $finder;
        $this->logConnectManager = $logConnectManager;
        $this->toolManager = $toolManager;
    }

    public function getName()
    {
        return 'log_connect_platform';
    }

    /**
     * @Route(
     *     "/platform/list",
     *     name="apiv2_log_connect_platform_list"
     * )
     *
     * @EXT\ParamConverter("user", converter="current_user", options={"allowAnonymous"=false})
     */
    public function logConnectPlatformListAction(User $user, Request $request): JsonResponse
    {
        $this->checkAdminToolAccess();
        $isAdmin = $this->authorization->isGranted('ROLE_ADMIN');
        $hiddenFilters = $isAdmin ?
            [] :
            ['hiddenFilters' => [
                'organizations' => array_map(function (Organization $organization) {
                    return $organization->getUuid();
                }, $user->getAdministratedOrganizations()->toArray()),
            ]];

        return new JsonResponse(
            $this->finder->search(LogConnectPlatform::class, array_merge(
                $request->query->all(),
                $hiddenFilters
            ))
        );
    }

    /**
     * @Route(
     *     "/workspace/{workspace}/list",
     *     name="apiv2_log_connect_workspace_list"
     * )
     *
     * @EXT\ParamConverter(
     *     "workspace",
     *     class="Claroline\CoreBundle\Entity\Workspace\Workspace",
     *     options={"mapping": {"workspace": "uuid"}}
     * )
     *
     * @return JsonResponse
     */
    public function logConnectWorkspaceListAction(Workspace $workspace, Request $request)
    {
        $this->checkWorkspaceToolAccess($workspace);

        return new JsonResponse(
            $this->finder->search(LogConnectWorkspace::class, array_merge(
                $request->query->all(),
                [
                    'hiddenFilters' => [
                        'workspace' => $workspace->getUuid(),
                    ],
                ]
            ))
        );
    }

    /**
     * @Route(
     *     "/resource/{resource}/list",
     *     name="apiv2_log_connect_resource_list"
     * )
     *
     * @EXT\ParamConverter(
     *     "resource",
     *     class="Claroline\CoreBundle\Entity\Resource\ResourceNode",
     *     options={"mapping": {"resource": "uuid"}}
     * )
     * @EXT\ParamConverter("user", converter="current_user", options={"allowAnonymous"=false})
     *
     * @return JsonResponse
     */
    public function logConnectResourceListAction(ResourceNode $resource, User $user, Request $request)
    {
        $hiddenFilters = ['resource' => $resource->getUuid()];

        if (!$this->authorization->isGranted('ADMINISTRATE', $resource->getWorkspace())) {
            $hiddenFilters['user'] = $user->getUuid();
        }

        return new JsonResponse(
            $this->finder->search(LogConnectResource::class, array_merge(
                $request->query->all(),
                ['hiddenFilters' => $hiddenFilters]
            ))
        );
    }

    /**
     * @param string $rights
     */
    private function checkAdminToolAccess($rights = 'OPEN')
    {
        $logsTool = $this->toolManager->getAdminToolByName('dashboard');

        if (is_null($logsTool) || !$this->authorization->isGranted($rights, $logsTool)) {
            throw new AccessDeniedException();
        }
    }

    private function checkWorkspaceToolAccess(Workspace $workspace, ?string $permission = 'OPEN'): void
    {
        if (!$this->authorization->isGranted(ToolPermissions::getPermission('dashboard', $permission), $workspace)) {
            throw new AccessDeniedException();
        }
    }
}
