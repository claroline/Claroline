<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Controller\APINew\Context;

use Claroline\AppBundle\API\Crud;
use Claroline\AppBundle\API\SerializerProvider;
use Claroline\AppBundle\Controller\RequestDecoderTrait;
use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Entity\Tool\OrderedTool;
use Claroline\CoreBundle\Entity\Tool\Tool;
use Claroline\CoreBundle\Entity\Tool\ToolRights;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Entity\Workspace\Workspace;
use Claroline\CoreBundle\Event\CatalogEvents\ToolEvents;
use Claroline\CoreBundle\Event\Tool\CloseToolEvent;
use Claroline\CoreBundle\Event\Tool\ConfigureToolEvent;
use Claroline\CoreBundle\Event\Tool\OpenToolEvent;
use Claroline\CoreBundle\Manager\LogConnectManager;
use Claroline\CoreBundle\Manager\Tool\ToolManager;
use Sensio\Bundle\FrameworkExtraBundle\Configuration as EXT;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

/**
 * @Route("/tool")
 */
class ToolController
{
    use RequestDecoderTrait;

    public function __construct(
        private readonly AuthorizationCheckerInterface $authorization,
        private readonly TokenStorageInterface $tokenStorage,
        private readonly ObjectManager $om,
        private readonly EventDispatcherInterface $eventDispatcher,
        private readonly Crud $crud,
        private readonly SerializerProvider $serializer,
        private readonly ToolManager $toolManager,
        private readonly LogConnectManager $logConnectManager
    ) {
    }

    /**
     * Opens a tool.
     *
     * @Route("/open/{name}/{context}/{contextId}", name="claro_tool_open", methods={"GET"})
     */
    public function openAction(string $name, string $context, ?string $contextId = null): JsonResponse
    {
        $orderedTool = $this->toolManager->getOrderedTool($name, $context, $contextId);
        if (!$orderedTool) {
            throw new NotFoundHttpException(sprintf('Tool "%s" for context "%s" not found', $name, $context));
        }

        if (!$this->authorization->isGranted('OPEN', $orderedTool)) {
            throw new AccessDeniedException();
        }

        $currentUser = $this->tokenStorage->getToken()->getUser();

        $openEvent = new OpenToolEvent(
            $name,
            $context,
            $orderedTool->getWorkspace(),
            $currentUser instanceof User ? $currentUser : null,
        );

        // Dispatch a generic tool event to be able to listen to all tool opening at once
        // (used by logs for example)
        $this->eventDispatcher->dispatch(
            $openEvent,
            ToolEvents::OPEN
        );

        // Dispatch a specialized tool event (used to grab custom data required by the tool)
        $this->eventDispatcher->dispatch(
            $openEvent,
            ToolEvents::getEventName(ToolEvents::OPEN, $context, $name)
        );

        return new JsonResponse(array_merge($openEvent->getData(), [
            'data' => $this->serializer->serialize($orderedTool),
        ]));
    }

    /**
     * @Route("/configure/{name}/{context}/{contextId}", name="apiv2_tool_configure", methods={"PUT"})
     */
    public function configureAction(Request $request, string $name, string $context, string $contextId = null): JsonResponse
    {
        /** @var OrderedTool|null $orderedTool */
        $orderedTool = $this->toolManager->getOrderedTool($name, $context, $contextId);
        if (!$orderedTool) {
            throw new NotFoundHttpException(sprintf('Tool "%s" not found', $name));
        }

        if (!$this->authorization->isGranted('EDIT', $orderedTool)) {
            throw new AccessDeniedException();
        }

        $data = $this->decodeRequest($request);

        $this->crud->update(OrderedTool::class, $data, [Crud::THROW_EXCEPTION]);

        $contextObject = null;
        if (Tool::WORKSPACE === $context) {
            $contextObject = $this->om->getRepository(Workspace::class)->findOneBy(['uuid' => $contextId]);
        }

        /** @var ConfigureToolEvent $event */
        $event = $this->eventDispatcher->dispatch(
            new ConfigureToolEvent($name, $context, $contextObject, $data),
            ToolEvents::getEventName(ToolEvents::CONFIGURE, $context, $name)
        );

        return new JsonResponse(array_merge($event->getData(), [
            'data' => $this->serializer->serialize($orderedTool),
        ]));
    }

    /**
     * @Route("/rights/{name}/{context}/{contextId}", name="apiv2_tool_get_rights", methods={"GET"})
     */
    public function getRightsAction(string $name, string $context, string $contextId = null): JsonResponse
    {
        /** @var OrderedTool|null $orderedTool */
        $orderedTool = $this->toolManager->getOrderedTool($name, $context, $contextId);
        if (!$orderedTool) {
            throw new NotFoundHttpException(sprintf('Tool "%s" not found', $name));
        }

        if (!$this->authorization->isGranted('ADMINISTRATE', $orderedTool)) {
            throw new AccessDeniedException();
        }

        return new JsonResponse(array_map(function (ToolRights $rights) {
            return $this->serializer->serialize($rights);
        }, $orderedTool->getRights()->toArray()));
    }

    /**
     * @Route("/rights/{name}/{context}/{contextId}", name="apiv2_tool_update_rights", methods={"PUT"})
     */
    public function updateRightsAction(Request $request, string $name, string $context, string $contextId = null): JsonResponse
    {
        /** @var OrderedTool|null $orderedTool */
        $orderedTool = $this->toolManager->getOrderedTool($name, $context, $contextId);
        if (!$orderedTool) {
            throw new NotFoundHttpException(sprintf('Tool "%s" not found', $name));
        }

        if (!$this->authorization->isGranted('ADMINISTRATE', $orderedTool)) {
            throw new AccessDeniedException();
        }

        $requestData = $this->decodeRequest($request);

        $this->om->startFlushSuite();

        $existingRights = $orderedTool->getRights();

        $roles = [];
        foreach ($requestData as $right) {
            if (!empty($right['id'])) {
                /** @var ToolRights $toolRights */
                $toolRights = $this->crud->update(ToolRights::class, array_merge($right, ['orderedToolId' => $orderedTool->getUuid()]), [Crud::THROW_EXCEPTION, Crud::NO_PERMISSIONS]);
            } else {
                /** @var ToolRights $toolRights */
                $toolRights = $this->crud->create(ToolRights::class, array_merge($right, ['orderedToolId' => $orderedTool->getUuid()]), [Crud::THROW_EXCEPTION, Crud::NO_PERMISSIONS]);
            }

            // keep reference to the created/update rights, it will be used later to know the ones to delete.
            // I don't use ToolRights id because there is a flush suite and it is not already generated.
            $roles[] = $toolRights->getRole()->getName();
        }

        // removes rights which no longer exists
        foreach ($existingRights as $existingRight) {
            if (!in_array($existingRight->getRole()->getName(), $roles)) {
                $this->crud->delete($existingRight);
            }
        }

        $this->om->endFlushSuite();

        return $this->getRightsAction($name, $context, $contextId);
    }

    /**
     * @Route("/close/{name}/{context}/{contextId}", name="claro_tool_close", methods={"PUT"})
     * @EXT\ParamConverter("user", converter="current_user", options={"allowAnonymous"=true})
     */
    public function closeAction(string $name, string $context, string $contextId = null, User $user = null): JsonResponse
    {
        /** @var OrderedTool|null $orderedTool */
        $orderedTool = $this->toolManager->getOrderedTool($name, $context, $contextId);
        if (!$orderedTool) {
            throw new NotFoundHttpException(sprintf('Tool "%s" not found', $name));
        }

        if (!$this->authorization->isGranted('OPEN', $orderedTool)) {
            throw new AccessDeniedException();
        }

        $this->eventDispatcher->dispatch(
            new CloseToolEvent($name, $context, $orderedTool->getWorkspace()),
            ToolEvents::CLOSE
        );

        if ($user) {
            // TODO : listen to close event instead
            $this->logConnectManager->computeToolDuration($user, $name, $context, $contextId);
        }

        return new JsonResponse(null, 204);
    }
}
