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
use Claroline\CommunityBundle\Repository\GroupRepository;
use Claroline\CommunityBundle\Repository\RoleRepository;
use Claroline\CommunityBundle\Repository\UserRepository;
use Claroline\CoreBundle\Entity\Group;
use Claroline\CoreBundle\Entity\Log\Connection\LogConnectPlatform;
use Claroline\CoreBundle\Entity\Log\Connection\LogConnectWorkspace;
use Claroline\CoreBundle\Entity\Log\Log;
use Claroline\CoreBundle\Entity\Organization\Organization;
use Claroline\CoreBundle\Entity\Resource\ResourceNode;
use Claroline\CoreBundle\Entity\Resource\ResourceType;
use Claroline\CoreBundle\Entity\Role;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Entity\Workspace\Workspace;
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
use Claroline\CoreBundle\Repository\WorkspaceRepository;
use Doctrine\ORM\EntityRepository;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class AnalyticsManager
{
    /** @var TokenStorageInterface */
    private $tokenStorage;

    /** @var WorkspaceRepository */
    private $workspaceRepo;

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

        $this->workspaceRepo = $objectManager->getRepository(Workspace::class);
        $this->userRepo = $objectManager->getRepository(User::class);
        $this->roleRepo = $objectManager->getRepository(Role::class);
        $this->groupRepo = $objectManager->getRepository(Group::class);
        $this->organizationRepo = $objectManager->getRepository(Organization::class);
        $this->resourceRepo = $objectManager->getRepository(ResourceNode::class);
        $this->resourceTypeRepo = $objectManager->getRepository(ResourceType::class);
        $this->logRepo = $objectManager->getRepository(Log::class);
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
            'storage' => $this->fileManager->getUsedStorage(), // TODO : filter by orga
            'connections' => [
                'count' => $this->logConnectPlatformRepo->countConnections($organizations),
                'avgTime' => $this->logConnectPlatformRepo->findAvgTime($organizations), // in seconds
            ],
            'users' => $this->userRepo->countUsers($organizations),
            'roles' => count($this->roleRepo->findAllPlatformRoles()),
            'groups' => count($this->groupRepo->findByOrganizations($organizations)),
            'workspaces' => $this->workspaceRepo->countNonPersonalWorkspaces($organizations),
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
}
