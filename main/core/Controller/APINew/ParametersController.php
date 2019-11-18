<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Controller\APINew;

use Claroline\CoreBundle\API\Serializer\ParametersSerializer;
use Claroline\CoreBundle\Library\Configuration\PlatformConfigurationHandler;
use Claroline\CoreBundle\Manager\AnalyticsManager;
use Claroline\CoreBundle\Manager\FileManager;
use Claroline\CoreBundle\Manager\VersionManager;
use Sensio\Bundle\FrameworkExtraBundle\Configuration as EXT;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * REST API to manage platform parameters.
 *
 * @EXT\Route("/parameters")
 */
class ParametersController
{
    /** @var PlatformConfigurationHandler */
    private $ch;
    /** @var AnalyticsManager */
    private $analyticsManager;
    /** @var VersionManager */
    private $versionManager;
    /** @var ParametersSerializer */
    private $serializer;
    /** @var FileManager */
    private $fileManager;

    /**
     * ParametersController constructor.
     *
     * @param PlatformConfigurationHandler $ch
     * @param AnalyticsManager             $analyticsManager
     * @param VersionManager               $versionManager
     * @param ParametersSerializer         $serializer
     * @param FileManager                  $fileManager
     */
    public function __construct(
        PlatformConfigurationHandler $ch,
        AnalyticsManager $analyticsManager,
        VersionManager $versionManager,
        ParametersSerializer $serializer,
        FileManager $fileManager
    ) {
        $this->ch = $ch;
        $this->serializer = $serializer;
        $this->versionManager = $versionManager;
        $this->analyticsManager = $analyticsManager;
        $this->fileManager = $fileManager;
    }

    /**
     * @EXT\Route("", name="apiv2_parameters_list")
     * @EXT\Method("GET")
     */
    public function listAction()
    {
        return new JsonResponse($this->serializer->serialize());
    }

    /**
     * @EXT\Route("", name="apiv2_parameters_update")
     * @EXT\Method("PUT")
     *
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function updateAction(Request $request)
    {
        $parameters = $this->serializer->deserialize(json_decode($request->getContent(), true));

        return new JsonResponse($parameters);
    }

    /**
     * @EXT\Route("/info", name="apiv2_platform_info")
     * @EXT\Method("GET")
     */
    public function getAction()
    {
        $parameters = $this->serializer->serialize();

        $usersCount = $this->analyticsManager->countEnabledUsers();
        $usersRolesCount = $this->analyticsManager->userRolesData(null);
        $totalUsers = array_shift($usersCount)['total'];
        $wsCount = $this->analyticsManager->countNonPersonalWorkspaces(null);
        $resourceCount = $this->analyticsManager->getResourceTypesCount(null, null);
        $otherResources = $this->analyticsManager->getOtherResourceTypesCount();

        $usedStorage = $this->fileManager->computeUsedStorage();
        $parameters['restrictions']['used_storage'] = $usedStorage;
        $parameters['restrictions']['max_storage_reached'] = isset($parameters['restrictions']['max_storage_size']) &&
            $parameters['restrictions']['max_storage_size'] &&
            $usedStorage >= $parameters['restrictions']['max_storage_size'];
        $this->serializer->deserialize($parameters);

        return new JsonResponse([
            'version' => $this->versionManager->getDistributionVersion(),
            'meta' => $parameters['meta'],
            'analytics' => [
                'resources' => $resourceCount,
                'workspaces' => $wsCount,
                'other' => $otherResources,
                'users' => $usersCount,
                'usersRoles' => $usersRolesCount,
                'totalUsers' => $totalUsers,
                'storage' => $usedStorage,
            ],
        ]);
    }
}
