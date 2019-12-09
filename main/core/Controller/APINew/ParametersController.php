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

use Claroline\AppBundle\API\Utils\ArrayUtils;
use Claroline\AppBundle\Controller\RequestDecoderTrait;
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
 */
class ParametersController
{
    use RequestDecoderTrait;

    /** @var PlatformConfigurationHandler */
    private $config;
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
        $this->config = $ch;
        $this->serializer = $serializer;
        $this->versionManager = $versionManager;
        $this->analyticsManager = $analyticsManager;
        $this->fileManager = $fileManager;
    }

    /**
     * @EXT\Route("/parameters", name="apiv2_parameters_update")
     * @EXT\Method("PUT")
     *
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function updateAction(Request $request)
    {
        $parametersData = $this->decodeRequest($request);

        // easy way to protect locked parameters (this may be done elsewhere)
        // avoid replacing locked parameters
        ArrayUtils::remove($parametersData, 'lockedParameters');
        // removes locked parameters values if any
        $locked = $this->config->getParameter('lockedParameters') ?? [];
        foreach ($locked as $lockedParam) {
            ArrayUtils::remove($parametersData, $lockedParam);
        }

        // save updated parameters
        $parameters = $this->serializer->deserialize($parametersData);

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
        $wsCount = $this->analyticsManager->countNonPersonalWorkspaces(null);
        $resourceCount = $this->analyticsManager->getResourceTypesCount(null, null);
        $otherResources = $this->analyticsManager->getOtherResourceTypesCount();

        // TODO : not the correct place to do it
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
                'storage' => $usedStorage,
            ],
        ]);
    }

    /**
     * @EXT\Route("/disable", name="apiv2_platform_disable")
     * @EXT\Method("PUT")
     *
     * @return JsonResponse
     */
    public function disableAction()
    {
        return new JsonResponse(null, 204);
    }

    /**
     * @EXT\Route("/maintenance/enable", name="apiv2_maintenance_enable")
     * @EXT\Method("PUT")
     *
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function enableMaintenanceAction(Request $request)
    {
        $this->config->setParameter('maintenance.enable', true);
        if (!empty($request->getContent())) {
            $this->config->setParameter('maintenance.message', $request->getContent());
        }

        return new JsonResponse(
            $this->config->getParameter('maintenance.message')
        );
    }

    /**
     * @EXT\Route("/maintenance/disable", name="apiv2_maintenance_disable")
     * @EXT\Method("PUT")
     *
     * @return JsonResponse
     */
    public function disableMaintenanceAction()
    {
        $this->config->setParameter('maintenance.enable', false);

        return new JsonResponse(null, 204);
    }
}
