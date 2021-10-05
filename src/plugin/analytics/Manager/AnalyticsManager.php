<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\AnalyticsBundle\Manager;

use Claroline\AppBundle\API\FinderProvider;
use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Entity\Log\Connection\LogConnectPlatform;
use Claroline\CoreBundle\Entity\Log\Connection\LogConnectWorkspace;
use Claroline\CoreBundle\Entity\Workspace\Workspace;
use Claroline\CoreBundle\Event\Log\LogResourceExportEvent;
use Claroline\CoreBundle\Event\Log\LogResourceReadEvent;
use Claroline\CoreBundle\Event\Log\LogWorkspaceToolReadEvent;
use Claroline\CoreBundle\Manager\FileManager;
use Claroline\CoreBundle\Manager\LogManager;
use Claroline\CoreBundle\Manager\UserManager;
use Claroline\CoreBundle\Manager\Workspace\WorkspaceManager;
use Claroline\CoreBundle\Repository\Log\Connection\LogConnectPlatformRepository;
use Claroline\CoreBundle\Repository\Log\Connection\LogConnectWorkspaceRepository;
use Claroline\CoreBundle\Repository\Log\LogRepository;
use Claroline\CoreBundle\Repository\Resource\ResourceNodeRepository;
use Claroline\CoreBundle\Repository\Resource\ResourceTypeRepository;
use Claroline\CoreBundle\Repository\User\GroupRepository;
use Claroline\CoreBundle\Repository\User\RoleRepository;
use Claroline\CoreBundle\Repository\User\UserRepository;
use Doctrine\ORM\EntityRepository;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class AnalyticsManager
{
    /** @var TokenStorageInterface */
    private $tokenStorage;

    /** @var UserRepository */
    private $userRepo;

    /** @var RoleRepository */
    private $roleRepo;

    /** @var GroupRepository */
    private $groupRepo;

    /** @var EntityRepository */
    private $organizationRepo;

    /** @var ResourceNodeRepository */
    private $resourceRepo;

    /** @var ResourceTypeRepository */
    private $resourceTypeRepo;

    /** @var LogRepository */
    private $logRepo;

    /** @var LogConnectPlatformRepository */
    private $logConnectPlatformRepo;

    /** @var LogConnectWorkspaceRepository */
    private $logConnectWorkspaceRepo;

    /** @var LogManager */
    private $logManager;

    /** @var UserManager */
    private $userManager;

    /** @var WorkspaceManager */
    private $workspaceManager;

    /** @var FileManager */
    private $fileManager;

    public function __construct(
        TokenStorageInterface $tokenStorage,
        ObjectManager $objectManager,
        LogManager $logManager,
        UserManager $userManager,
        WorkspaceManager $workspaceManager,
        FileManager $fileManager
    ) {
        $this->tokenStorage = $tokenStorage;
        $this->logManager = $logManager;
        $this->userManager = $userManager;
        $this->workspaceManager = $workspaceManager;
        $this->fileManager = $fileManager;

        $this->userRepo = $objectManager->getRepository('ClarolineCoreBundle:User');
        $this->roleRepo = $objectManager->getRepository('ClarolineCoreBundle:Role');
        $this->groupRepo = $objectManager->getRepository('ClarolineCoreBundle:Group');
        $this->organizationRepo = $objectManager->getRepository('ClarolineCoreBundle:Organization\Organization');
        $this->resourceRepo = $objectManager->getRepository('ClarolineCoreBundle:Resource\ResourceNode');
        $this->resourceTypeRepo = $objectManager->getRepository('ClarolineCoreBundle:Resource\ResourceType');
        $this->logRepo = $objectManager->getRepository('ClarolineCoreBundle:Log\Log');
        $this->logConnectPlatformRepo = $objectManager->getRepository(LogConnectPlatform::class);
        $this->logConnectWorkspaceRepo = $objectManager->getRepository(LogConnectWorkspace::class);
    }

    /**
     * @todo count all in DQL
     */
    public function count(Workspace $workspace = null): array
    {
        // get values for workspace only
        if ($workspace) {
            return [
                'resources' => $this->resourceRepo->countActiveResources([$workspace]),
                'storage' => $this->workspaceManager->getUsedStorage($workspace),
                'connections' => [
                    'count' => $this->logConnectWorkspaceRepo->countConnections($workspace),
                    'avgTime' => $this->logConnectWorkspaceRepo->findAvgTime($workspace), // in seconds
                ],
                'users' => count($this->userRepo->findByWorkspaces([$workspace])),
                'roles' => count($this->roleRepo->findBy(['workspace' => $workspace])),
                'groups' => count($this->groupRepo->findByWorkspace($workspace)),
            ];
        }

        // get values for user administrated organizations
        $organizations = [];
        $user = $this->tokenStorage->getToken()->getUser();
        if (!$user->hasRole('ROLE_ADMIN')) {
            $organizations = $user->getAdministratedOrganizations()->toArray();
        }

        return [
            'resources' => $this->resourceRepo->countActiveResources([], $organizations),
            'storage' => $this->fileManager->computeUsedStorage(), // TODO : filter by orga
            'connections' => [
                'count' => $this->logConnectPlatformRepo->countConnections($organizations),
                'avgTime' => $this->logConnectPlatformRepo->findAvgTime($organizations), // in seconds
            ],
            'users' => $this->userRepo->countUsers($organizations),
            'roles' => count($this->roleRepo->findAllPlatformRoles()),
            'groups' => count($this->groupRepo->findByOrganizations($organizations)),
            'workspaces' => $this->workspaceManager->getNbNonPersonalWorkspaces($organizations),
            'organizations' => !empty($organizations) ?
                count($organizations) :
                $this->organizationRepo->count([]),
        ];
    }

    public function getResourceTypesCount(Workspace $workspace = null, $organizations = null)
    {
        $resourceTypes = $this->resourceTypeRepo->countResourcesByType($workspace, $organizations);
        $chartData = [];
        foreach ($resourceTypes as $type) {
            $chartData["rt-${type['id']}"] = [
                'xData' => $type['name'],
                'yData' => floatval($type['total']),
            ];
        }

        return $chartData;
    }

    public function getDailyActions(array $finderParams = [])
    {
        return $this->logManager->getChartData($this->formatQueryParams($finderParams));
    }

    public function topWorkspaceByAction(array $finderParams)
    {
        $query = $this->formatQueryParams($finderParams);

        if (!isset($query['filters']['action'])) {
            $query['filters']['action'] = LogWorkspaceToolReadEvent::ACTION;
        }
        $queryParams = FinderProvider::parseQueryParams($query);

        return $this->logRepo->findTopWorkspaceByAction($queryParams['allFilters'], $queryParams['limit']);
    }

    public function topResourcesByAction(array $finderParams, $onlyMedia = false)
    {
        $query = $this->formatQueryParams($finderParams);

        if (!isset($query['filters']['action'])) {
            $query['filters']['action'] = LogResourceReadEvent::ACTION;
        }

        if ($onlyMedia) {
            $query['filters']['resourceType'] = 'file';
        }
        $queryParams = FinderProvider::parseQueryParams($query);

        return $this->logRepo->findTopResourcesByAction($queryParams['allFilters'], $queryParams['limit']);
    }

    public function userRolesData(Workspace $workspace = null, $organizations = null)
    {
        if ($workspace) {
            return $this->workspaceManager->countUsersForRoles($workspace);
        }

        return $this->userManager->countUsersForPlatformRoles($organizations);
    }

    private function formatQueryParams(array $finderParams = [])
    {
        $filters = isset($finderParams['filters']) ? $finderParams['filters'] : [];
        $hiddenFilters = isset($finderParams['hiddenFilters']) ? $finderParams['hiddenFilters'] : [];

        return [
            'filters' => $this->formatDateRange($filters),
            'hiddenFilters' => $hiddenFilters,
        ];
    }

    private function formatDateRange(array $filters)
    {
        // Default 30 days analytics
        if (!isset($filters['dateLog'])) {
            $date = new \DateTime('now');
            $date->setTime(0, 0, 0);
            $date->sub(new \DateInterval('P30D'));
            $filters['dateLog'] = clone $date;
        }

        if (!isset($filters['dateTo'])) {
            $date = clone $filters['dateLog'];
            $date->add(new \DateInterval('P30D'));
            $date->setTime(23, 59, 59);
            $filters['dateTo'] = clone $date;
        }

        return $filters;
    }

    public function getTopActions(array $finderParams = [])
    {
        $finderParams['filters'] = isset($finderParams['filters']) ? $finderParams['filters'] : [];
        $topType = isset($finderParams['filters']['type']) ? $finderParams['filters']['type'] : 'top_users_connections';
        unset($finderParams['filters']['type']);
        $organizations = isset($finderParams['hiddenFilters']['organization']) ?
            $finderParams['hiddenFilters']['organization'] :
            null;
        $finderParams['limit'] = isset($finderParams['limit']) ? intval($finderParams['limit']) : 10;
        switch ($topType) {
            case 'top_extension':
                $listData = $this->resourceRepo->findMimeTypesWithMostResources($finderParams['limit'], $organizations);
                break;
            case 'top_workspaces_resources':
                $listData = $this->workspaceManager->getWorkspacesWithMostResources($finderParams['limit'], $organizations);
                break;
            case 'top_workspaces_connections':
                $finderParams['filters']['action'] = LogWorkspaceToolReadEvent::ACTION;
                $listData = $this->topWorkspaceByAction($finderParams);
                break;
            case 'top_resources_views':
                $finderParams['filters']['action'] = LogResourceReadEvent::ACTION;
                $listData = $this->topResourcesByAction($finderParams);
                break;
            case 'top_resources_downloads':
                $finderParams['filters']['action'] = LogResourceExportEvent::ACTION;
                $listData = $this->topResourcesByAction($finderParams);
                break;
            case 'top_users_workspaces_enrolled':
                $listData = $this->userManager->getUsersEnrolledInMostWorkspaces($finderParams['limit'], $organizations);
                break;
            case 'top_users_workspaces_owners':
                $listData = $this->userManager->getUsersOwnersOfMostWorkspaces($finderParams['limit'], $organizations);
                break;
            case 'top_media_views':
                $finderParams['filters']['action'] = LogResourceReadEvent::ACTION;
                $listData = $this->topResourcesByAction($finderParams, true);
                break;
            case 'top_users_connections':
            default:
                $finderParams['filters']['action'] = 'user-login';
                $finderParams['sortBy'] = '-actions';
                $listData = $this->logManager->getUserActionsList($finderParams);
                $listData = $listData['data'];
                break;
        }

        foreach ($listData as $idx => &$data) {
            if (!isset($data['id'])) {
                $data['id'] = "top-{$idx}";
            }
        }

        return [
            'data' => $listData,
            'filters' => [
                ['property' => 'type', 'value' => $topType],
            ],
            'page' => 0,
            'pageSize' => $finderParams['limit'],
            'sortBy' => null,
            'totalResults' => count($listData),
        ];
    }
}
