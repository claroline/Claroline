<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CommunityBundle\Controller;

use Claroline\AppBundle\API\Utils\ArrayUtils;
use Claroline\AppBundle\Controller\AbstractSecurityController;
use Claroline\AppBundle\Controller\RequestDecoderTrait;
use Claroline\CoreBundle\API\Serializer\ParametersSerializer;
use Claroline\CoreBundle\Library\Configuration\PlatformConfigurationHandler;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

/**
 * @Route("/community")
 */
class CommunityController extends AbstractSecurityController
{
    use RequestDecoderTrait;

    /** @var AuthorizationCheckerInterface */
    private $authorization;
    /** @var PlatformConfigurationHandler */
    private $config;
    /** @var ParametersSerializer */
    private $serializer;

    public function __construct(
        AuthorizationCheckerInterface $authorization,
        PlatformConfigurationHandler $ch,
        ParametersSerializer $serializer
    ) {
        $this->authorization = $authorization;
        $this->config = $ch;
        $this->serializer = $serializer;
    }

    /**
     * Manages Community tool parameters. To replace later by the tool configuration system.
     *
     * @Route("/parameters", name="apiv2_community_parameters", methods={"PUT"})
     */
    public function updateAction(Request $request): JsonResponse
    {
        $this->canOpenAdminTool('community');

        $parametersData = $this->decodeRequest($request);

        // only keep parameters linked to community to avoid exposing all the platform parameters here
        $communityParameters = [];
        if (isset($parametersData['registration'])) {
            $communityParameters['registration'] = $parametersData['registration'];
        }
        if (isset($parametersData['authentication'])) {
            $communityParameters['authentication'] = $parametersData['authentication'];
        }
        if (isset($parametersData['profile'])) {
            $communityParameters['profile'] = $parametersData['profile'];
        }
        if (isset($parametersData['community'])) {
            $communityParameters['community'] = $parametersData['community'];
        }

        // removes locked parameters values if any
        $locked = $this->config->getParameter('lockedParameters') ?? [];
        foreach ($locked as $lockedParam) {
            ArrayUtils::remove($communityParameters, $lockedParam);
        }

        // save updated parameters
        $parameters = $this->serializer->deserialize($communityParameters);

        return new JsonResponse($parameters);
    }
}
