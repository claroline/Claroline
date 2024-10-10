<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Controller\Platform;

use Claroline\AppBundle\API\Utils\ArrayUtils;
use Claroline\AppBundle\Controller\AbstractSecurityController;
use Claroline\AppBundle\Controller\RequestDecoderTrait;
use Claroline\CoreBundle\API\Serializer\ParametersSerializer;
use Claroline\CoreBundle\Library\Configuration\PlatformConfigurationHandler;
use Claroline\CoreBundle\Manager\VersionManager;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

/**
 * REST API to manage platform parameters.
 */
class ParametersController extends AbstractSecurityController
{
    use RequestDecoderTrait;

    public function __construct(
        AuthorizationCheckerInterface $authorization,
        private readonly EventDispatcherInterface $eventDispatcher,
        private readonly PlatformConfigurationHandler $config,
        private readonly VersionManager $versionManager,
        private readonly ParametersSerializer $serializer
    ) {
        $this->setAuthorizationChecker($authorization);
    }

    #[Route(path: '/parameters', name: 'apiv2_parameters_update', methods: ['PUT'])]
    public function updateAction(Request $request): JsonResponse
    {
        $this->canOpenAdminTool('parameters');

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

    #[Route(path: '/version', name: 'apiv2_platform_version', methods: ['GET'])]
    public function getVersionAction(Request $request): JsonResponse
    {
        return new JsonResponse([
            'version' => $this->versionManager->getCurrent(),
            'changelogs' => $this->versionManager->getChangelogs($request->getLocale()),
        ]);
    }
}
