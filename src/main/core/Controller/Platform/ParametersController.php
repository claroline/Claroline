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
use Claroline\AppBundle\Event\Platform\EnableEvent;
use Claroline\AppBundle\Event\Platform\ExtendEvent;
use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\API\Serializer\ParametersSerializer;
use Claroline\CoreBundle\Entity\Group;
use Claroline\CoreBundle\Entity\Organization\Organization;
use Claroline\CoreBundle\Entity\Resource\ResourceNode;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Entity\Workspace\Workspace;
use Claroline\CoreBundle\Library\Configuration\PlatformConfigurationHandler;
use Claroline\CoreBundle\Library\Normalizer\DateNormalizer;
use Claroline\CoreBundle\Manager\FileManager;
use Claroline\CoreBundle\Manager\VersionManager;
use Claroline\CoreBundle\Security\PlatformRoles;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
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
        private readonly ObjectManager $om,
        private readonly FileManager $fileManager,
        private readonly VersionManager $versionManager,
        private readonly ParametersSerializer $serializer
    ) {
        $this->setAuthorizationChecker($authorization);
    }

    /**
     * @Route("/parameters", name="apiv2_parameters_update", methods={"PUT"})
     */
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

    /**
     * @Route("/version", name="apiv2_platform_version", methods={"GET"})
     */
    public function getVersionAction(Request $request): JsonResponse
    {
        return new JsonResponse([
            'version' => $this->versionManager->getCurrent(),
            'changelogs' => $this->versionManager->getChangelogs($request->getLocale()),
        ]);
    }

    /**
     * @Route("/info", name="apiv2_platform_info", methods={"GET"})
     */
    public function getAction(): JsonResponse
    {
        $this->checkPermission(PlatformRoles::ADMIN, null, [], true);

        return new JsonResponse([
            'version' => $this->versionManager->getCurrent(),
            'meta' => $this->config->getParameter('meta'),
            'analytics' => [  // TODO : add analytics through eventing to avoid hard dependencies
                'resources' => $this->om->getRepository(ResourceNode::class)->countActiveResources(),
                'storage' => $this->fileManager->getUsedStorage(),
                'users' => $this->om->getRepository(User::class)->countUsers(),
                //'roles' => count($this->om->getRepository(Role::class)->findAllPlatformRoles()),
                'groups' => $this->om->getRepository(Group::class)->count([]),
                'workspaces' => $this->om->getRepository(Workspace::class)->countNonPersonalWorkspaces(),
                'organizations' => $this->om->getRepository(Organization::class)->count([]),
            ],
        ]);
    }

    /**
     * Enables the platform.
     *
     * @Route("/enable", name="apiv2_platform_enable", methods={"PUT"})
     */
    public function enableAction(): JsonResponse
    {
        $this->checkPermission(PlatformRoles::ADMIN, null, [], true);

        $event = new EnableEvent();
        $this->eventDispatcher->dispatch($event, 'platform.enable');
        if ($event->isCanceled()) {
            return new JsonResponse($event->getCancellationMessage(), 422); // not sure it's the correct status
        }

        $this->config->setParameter('restrictions.disabled', false);

        return new JsonResponse(null, 204);
    }

    /**
     * Extends the period of availability of the platform.
     *
     * @Route("/extend", name="apiv2_platform_extend", methods={"PUT"})
     */
    public function extendAction(): JsonResponse
    {
        $this->checkPermission(PlatformRoles::ADMIN, null, [], true);

        $newEnd = null;

        $dates = $this->config->getParameter('restrictions.dates');
        if (!empty($dates) && !empty($dates[1])) {
            // only do something if there is an end date
            // by default extend for 1 week
            // event listener can override it
            $event = new ExtendEvent(DateNormalizer::denormalize($dates[1])->add(new \DateInterval('P7D')));
            $this->eventDispatcher->dispatch($event, 'platform.extend');

            if ($event->isCanceled()) {
                return new JsonResponse($event->getCancellationMessage(), 422); // not sure it's the correct status
            }

            $newEnd = $event->getEnd();

            // replace date in config
            $dates[1] = DateNormalizer::normalize($newEnd);
            $this->config->setParameter('restrictions.dates', $dates);
        }

        return new JsonResponse($newEnd, 204);
    }

    /**
     * @Route("/disable", name="apiv2_platform_disable", methods={"PUT"})
     */
    public function disableAction(): JsonResponse
    {
        $this->checkPermission(PlatformRoles::ADMIN, null, [], true);

        $this->config->setParameter('restrictions.disabled', true);

        return new JsonResponse(null, 204);
    }

    /**
     * @Route("/maintenance/enable", name="apiv2_maintenance_enable", methods={"PUT"})
     */
    public function enableMaintenanceAction(Request $request): JsonResponse
    {
        $this->checkPermission(PlatformRoles::ADMIN, null, [], true);

        $this->config->setParameter('maintenance.enable', true);
        if (!empty($request->getContent())) {
            $this->config->setParameter('maintenance.message', $request->getContent());
        }

        return new JsonResponse(
            $this->config->getParameter('maintenance.message')
        );
    }

    /**
     * @Route("/maintenance/disable", name="apiv2_maintenance_disable", methods={"PUT"})
     */
    public function disableMaintenanceAction(): JsonResponse
    {
        $this->checkPermission(PlatformRoles::ADMIN, null, [], true);

        $this->config->setParameter('maintenance.enable', false);

        return new JsonResponse(null, 204);
    }
}
