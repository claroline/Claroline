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
use Claroline\CoreBundle\Entity\Log\Connection\LogConnectWorkspace;
use Claroline\CoreBundle\Entity\Organization\Organization;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Entity\Workspace\Workspace;
use Claroline\CoreBundle\Manager\ToolManager;
use JMS\DiExtraBundle\Annotation as DI;
use Sensio\Bundle\FrameworkExtraBundle\Configuration as EXT;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Translation\TranslatorInterface;

/**
 * @EXT\Route("/log_connect")
 */
class LogConnectController
{
    /** @var AuthorizationCheckerInterface */
    private $authorization;

    /** @var FinderProvider */
    private $finder;

    /** @var ToolManager */
    private $toolManager;

    /** @var TranslatorInterface */
    private $translator;

    /**
     * CourseController constructor.
     *
     * @DI\InjectParams({
     *     "authorization" = @DI\Inject("security.authorization_checker"),
     *     "finder"        = @DI\Inject("claroline.api.finder"),
     *     "toolManager"   = @DI\Inject("claroline.manager.tool_manager"),
     *     "translator"    = @DI\Inject("translator")
     * })
     *
     * @param AuthorizationCheckerInterface $authorization
     * @param FinderProvider                $finder
     * @param ToolManager                   $toolManager
     * @param TranslatorInterface           $translator
     */
    public function __construct(
        AuthorizationCheckerInterface $authorization,
        FinderProvider $finder,
        ToolManager $toolManager,
        TranslatorInterface $translator
    ) {
        $this->authorization = $authorization;
        $this->finder = $finder;
        $this->toolManager = $toolManager;
        $this->translator = $translator;
    }

    public function getName()
    {
        return 'log_connect_platform';
    }

    /**
     * @EXT\Route(
     *     "/platform/list",
     *     name="apiv2_log_connect_platform_list"
     * )
     * @EXT\ParamConverter("user", converter="current_user", options={"allowAnonymous"=false})
     *
     * @param User    $user
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function logConnectPlatformListAction(User $user, Request $request)
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
            $this->finder->search('Claroline\CoreBundle\Entity\Log\Connection\LogConnectPlatform', array_merge(
                $request->query->all(),
                $hiddenFilters
            ))
        );
    }

    /**
     * @EXT\Route(
     *     "/platform/csv",
     *     name="apiv2_log_connect_platform_list_csv"
     * )
     * @EXT\Method("GET")
     * @EXT\ParamConverter("user", converter="current_user", options={"allowAnonymous"=false})
     *
     * @param User    $user
     * @param Request $request
     *
     * @return StreamedResponse
     */
    public function logConnectPlatformListCsvAction(User $user, Request $request)
    {
        $this->checkAdminToolAccess();
        $isAdmin = $this->authorization->isGranted('ROLE_ADMIN');
        $query = $request->query->all();
        $filters = isset($query['filters']) ? $query['filters'] : [];
        $sortBy = null;

        if (isset($query['sortBy'])) {
            $direction = '-' === substr($query['sortBy'], 0, 1) ? -1 : 1;
            $property = 1 === $direction ? $query['sortBy'] : substr($query['sortBy'], 1);
            $sortBy = ['property' => $property, 'direction' => $direction];
        }

        if (!$isAdmin) {
            $filters['organizations'] = array_map(function (Organization $organization) {
                return $organization->getUuid();
            }, $user->getAdministratedOrganizations()->toArray());
        }
        $connections = $this->finder->get(LogConnectPlatform::class)->find($filters, $sortBy);

        // Prepare CSV file
        $handle = fopen('php://output', 'w+');
        fputcsv($handle, [
            $this->translator->trans('date', [], 'platform'),
            $this->translator->trans('user', [], 'platform'),
            $this->translator->trans('duration', [], 'platform'),
        ], ';', '"');

        foreach ($connections as $connection) {
            $duration = $connection->getDuration();
            $durationString = null;

            if (!is_null($duration)) {
                $hours = floor($duration / 3600);
                $duration %= 3600;
                $minutes = floor($duration / 60);
                $seconds = $duration % 60;

                $durationString = "{$hours}:";
                $durationString .= 10 > $minutes ? "0{$minutes}:" : "{$minutes}:";
                $durationString .= 10 > $seconds ? "0{$seconds}" : "{$seconds}";
            }
            fputcsv($handle, [
                $connection->getConnectionDate()->format('Y-m-d H:i:s'),
                $connection->getUser()->getFirstName().' '.$connection->getUser()->getLastName(),
                $durationString,
            ], ';', '"');
        }
        fclose($handle);

        $downloadDate = date('Y-m-d_H-i-s');

        return new StreamedResponse(
            function () use ($handle) {
                $handle;
            },
            200,
            [
                'Content-Type' => 'application/force-download',
                'Content-Disposition' => 'attachment; filename="connection_time_platform_'.$downloadDate.'.csv"',
            ]
        );
    }

    /**
     * @EXT\Route(
     *     "/workspace/{workspace}/list",
     *     name="apiv2_log_connect_workspace_list"
     * )
     * @EXT\ParamConverter(
     *     "workspace",
     *     class="ClarolineCoreBundle:Workspace\Workspace",
     *     options={"mapping": {"workspace": "id"}}
     * )
     *
     * @param Workspace $workspace
     * @param Request   $request
     *
     * @return JsonResponse
     */
    public function logConnectWorkspaceListAction(Workspace $workspace, Request $request)
    {
        $this->checkWorkspaceToolAccess($workspace);

        return new JsonResponse(
            $this->finder->search('Claroline\CoreBundle\Entity\Log\Connection\LogConnectWorkspace', array_merge(
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
     * @EXT\Route(
     *     "/workspace/{workspace}/csv",
     *     name="apiv2_log_connect_workspace_list_csv"
     * )
     * @EXT\Method("GET")
     * @EXT\ParamConverter(
     *     "workspace",
     *     class="ClarolineCoreBundle:Workspace\Workspace",
     *     options={"mapping": {"workspace": "id"}}
     * )
     *
     * @param Workspace $workspace
     * @param Request   $request
     *
     * @return StreamedResponse
     */
    public function logConnectWorkspaceListCsvAction(Workspace $workspace, Request $request)
    {
        $this->checkWorkspaceToolAccess($workspace);
        $query = $request->query->all();
        $filters = isset($query['filters']) ? $query['filters'] : [];
        $sortBy = null;

        if (isset($query['sortBy'])) {
            $direction = '-' === substr($query['sortBy'], 0, 1) ? -1 : 1;
            $property = 1 === $direction ? $query['sortBy'] : substr($query['sortBy'], 1);
            $sortBy = ['property' => $property, 'direction' => $direction];
        }
        $filters['workspace'] = $workspace->getUuid();

        $connections = $this->finder->get(LogConnectWorkspace::class)->find($filters, $sortBy);

        // Prepare CSV file
        $handle = fopen('php://output', 'w+');
        fputcsv($handle, [
            $this->translator->trans('date', [], 'platform'),
            $this->translator->trans('user', [], 'platform'),
            $this->translator->trans('duration', [], 'platform'),
        ], ';', '"');

        foreach ($connections as $connection) {
            $duration = $connection->getDuration();
            $durationString = null;

            if (!is_null($duration)) {
                $hours = floor($duration / 3600);
                $duration %= 3600;
                $minutes = floor($duration / 60);
                $seconds = $duration % 60;

                $durationString = "{$hours}:";
                $durationString .= 10 > $minutes ? "0{$minutes}:" : "{$minutes}:";
                $durationString .= 10 > $seconds ? "0{$seconds}" : "{$seconds}";
            }
            fputcsv($handle, [
                $connection->getConnectionDate()->format('Y-m-d H:i:s'),
                $connection->getUser()->getFirstName().' '.$connection->getUser()->getLastName(),
                $durationString,
            ], ';', '"');
        }
        fclose($handle);

        $downloadDate = date('Y-m-d_H-i-s');

        return new StreamedResponse(
            function () use ($handle) {
                $handle;
            },
            200,
            [
                'Content-Type' => 'application/force-download',
                'Content-Disposition' => 'attachment; filename="connection_time_workspace_'.$workspace->getUuid().'_'.$downloadDate.'.csv"',
            ]
        );
    }

    /**
     * @param string $rights
     */
    private function checkAdminToolAccess($rights = 'OPEN')
    {
        $logsTool = $this->toolManager->getAdminToolByName('platform_logs');

        if (is_null($logsTool) || !$this->authorization->isGranted($rights, $logsTool)) {
            throw new AccessDeniedException();
        }
    }

    /**
     * @param Workspace $workspace
     * @param string    $rights
     */
    private function checkWorkspaceToolAccess(Workspace $workspace, $rights = 'OPEN')
    {
        if (!$this->authorization->isGranted(['logs', $rights], $workspace)) {
            throw new AccessDeniedException();
        }
    }
}
