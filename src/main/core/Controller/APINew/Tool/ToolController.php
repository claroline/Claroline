<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Controller\APINew\Tool;

use Claroline\AppBundle\API\Crud;
use Claroline\AppBundle\API\SerializerProvider;
use Claroline\AppBundle\Controller\RequestDecoderTrait;
use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Entity\Role;
use Claroline\CoreBundle\Entity\Tool\OrderedTool;
use Claroline\CoreBundle\Entity\Tool\Tool;
use Claroline\CoreBundle\Entity\Tool\ToolRights;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Entity\Workspace\Workspace;
use Claroline\CoreBundle\Event\Tool\ConfigureToolEvent;
use Claroline\CoreBundle\Manager\LogConnectManager;
use Claroline\CoreBundle\Manager\Tool\ToolManager;
use Claroline\CoreBundle\Manager\Tool\ToolMaskDecoderManager;
use Sensio\Bundle\FrameworkExtraBundle\Configuration as EXT;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

/**
 * @Route("/tool")
 */
class ToolController
{
    use RequestDecoderTrait;

    /** @var AuthorizationCheckerInterface */
    private $authorization;
    /** @var ObjectManager */
    private $om;
    /** @var EventDispatcherInterface */
    private $eventDispatcher;
    /** @var Crud */
    private $crud;
    /** @var SerializerProvider */
    private $serializer;
    /** @var ToolManager */
    private $toolManager;
    /** @var ToolMaskDecoderManager */
    private $maskManager;
    /** @var LogConnectManager */
    private $logConnectManager;

    /**
     * ToolController constructor.
     */
    public function __construct(
        AuthorizationCheckerInterface $authorization,
        ObjectManager $om,
        EventDispatcherInterface $eventDispatcher,
        Crud $crud,
        SerializerProvider $serializer,
        ToolManager $toolManager,
        ToolMaskDecoderManager $maskManager,
        LogConnectManager $logConnectManager
    ) {
        $this->authorization = $authorization;
        $this->om = $om;
        $this->eventDispatcher = $eventDispatcher;
        $this->crud = $crud;
        $this->serializer = $serializer;
        $this->toolManager = $toolManager;
        $this->maskManager = $maskManager;
        $this->logConnectManager = $logConnectManager;
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
        $event = $this->eventDispatcher->dispatch(new ConfigureToolEvent($data, $contextObject), $context.'.'.$name.'.configure');

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

        return new JsonResponse(array_map(function (ToolRights $rights) use ($orderedTool) {
            $role = $rights->getRole();

            $data = [
                'id' => $rights->getId(),
                'translationKey' => $role->getTranslationKey(),
                'name' => $role->getName(),
                'permissions' => $this->maskManager->decodeMask($rights->getMask(), $orderedTool->getTool()),
                'workspace' => null,
            ];

            if ($role->getWorkspace()) {
                $data['workspace']['code'] = $role->getWorkspace()->getCode();
            }

            return $data;
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

        $existingRights = $orderedTool->getRights();

        $roles = [];
        foreach ($requestData as $right) {
            /** @var Role $role */
            $role = $this->om->getRepository(Role::class)->findOneBy(['name' => $right['name']]);
            if ($role) {
                $this->toolManager->setPermissions($right['permissions'], $orderedTool, $role);
                $roles[] = $role->getName();
            }
        }

        // removes rights which no longer exists
        foreach ($existingRights as $existingRight) {
            if (!in_array($existingRight->getRole()->getName(), $roles)) {
                $orderedTool->removeRight($existingRight);
            }
        }

        $this->om->persist($orderedTool);
        $this->om->flush();

        return $this->getRightsAction($name, $context, $contextId);
    }

    /**
     * @Route("/close/{name}/{context}/{contextId}", name="apiv2_tool_close", methods={"PUT"})
     * @EXT\ParamConverter("user", converter="current_user", options={"allowAnonymous"=true})
     */
    public function closeAction(string $name, string $context, string $contextId = null, User $user = null): JsonResponse
    {
        if ($user) {
            $this->logConnectManager->computeToolDuration($user, $name, $context, $contextId);
        }

        return new JsonResponse(null, 204);
    }
}
