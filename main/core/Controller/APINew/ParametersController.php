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
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * REST API to manage platform parameters.
 *
 * @Route("/parameters")
 */
class ParametersController
{
    /**
     * ParametersController constructor.
     *
     * @param PlatformConfigurationHandler $ch
     * @param ParametersSerializer         $serializer
     * @param AnalyticsManager             $analyticsManager
     */
    public function __construct(
        PlatformConfigurationHandler $ch,
        AnalyticsManager $analyticsManager,
        ParametersSerializer $serializer
    ) {
        $this->ch = $ch;
        $this->serializer = $serializer;
        $this->analyticsManager = $analyticsManager;
    }

    /**
     * @Route("", name="apiv2_parameters_list")
     * @Method("GET")
     */
    public function listAction()
    {
        return new JsonResponse($this->serializer->serialize());
    }

    /**
     * @Route("", name="apiv2_parameters_update")
     * @Method("PUT")
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
     * @Route("/details", name="apiv2_platform_details")
     * @Method("GET")
     */
    public function getDetails()
    {
        $parameters = $this->serializer->serialize();
        $data['workspace'] = $parameters['workspace'];
        $data['security']['platform_init_date'] = $parameters['security']['platform_init_date'];
        $data['security']['platform_limit_date'] = $parameters['security']['platform_limit_date'];

        $usersCount = $this->analyticsManager->userRolesData(null);
        $totalUsers = array_shift($usersCount)['total'];
        $wsCount = $this->analyticsManager->countNonPersonalWorkspaces(null);
        $resourceCount = $this->analyticsManager->getResourceTypesCount(null, null);
        $otherResources = $this->analyticsManager->getOtherResourceTypesCount();

        $analytics = ['analytics' => [
          'resources' => $resourceCount,
          'workspaces' => $wsCount,
          'other' => $otherResources,
          'users' => $usersCount,
          'totalUsers' => $totalUsers,
        ]];

        return new JsonResponse(array_merge($analytics, $data));
    }
}
