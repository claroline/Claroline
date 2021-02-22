<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Controller;

use Claroline\AppBundle\API\Options;
use Claroline\AppBundle\API\SerializerProvider;
use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Entity\Resource\ResourceNode;
use Claroline\CoreBundle\Entity\Role;
use Claroline\CoreBundle\Entity\Tool\OrderedTool;
use Claroline\CoreBundle\Entity\Tool\Tool;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Entity\Workspace\Shortcuts;
use Claroline\CoreBundle\Entity\Workspace\Workspace;
use Claroline\CoreBundle\Event\Log\LogWorkspaceEnterEvent;
use Claroline\CoreBundle\Event\Log\LogWorkspaceToolReadEvent;
use Claroline\CoreBundle\Event\Tool\OpenToolEvent;
use Claroline\CoreBundle\Event\Workspace\OpenWorkspaceEvent;
use Claroline\CoreBundle\Manager\Tool\ToolManager;
use Claroline\CoreBundle\Manager\Workspace\EvaluationManager;
use Claroline\CoreBundle\Manager\Workspace\WorkspaceManager;
use Claroline\CoreBundle\Manager\Workspace\WorkspaceRestrictionsManager;
use Sensio\Bundle\FrameworkExtraBundle\Configuration as EXT;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Translation\TranslatorInterface;

/**
 * @Route("/workspaces", options={"expose" = true})
 */
class WorkspaceController
{
    /** @var AuthorizationCheckerInterface */
    private $authorization;
    /** @var ObjectManager */
    private $om;
    /** @var EventDispatcherInterface */
    private $eventDispatcher;
    /** @var TokenStorageInterface */
    private $tokenStorage;
    /** @var SerializerProvider */
    private $serializer;
    /** @var ToolManager */
    private $toolManager;
    /** @var TranslatorInterface */
    private $translator;
    /** @var WorkspaceManager */
    private $manager;
    /** @var WorkspaceRestrictionsManager */
    private $restrictionsManager;
    /** @var EvaluationManager */
    private $evaluationManager;

    /**
     * WorkspaceController constructor.
     */
    public function __construct(
        AuthorizationCheckerInterface $authorization,
        ObjectManager $om,
        EventDispatcherInterface $eventDispatcher,
        TokenStorageInterface $tokenStorage,
        SerializerProvider $serializer,
        ToolManager $toolManager,
        TranslatorInterface $translator,
        WorkspaceManager $manager,
        WorkspaceRestrictionsManager $restrictionsManager,
        EvaluationManager $evaluationManager
    ) {
        $this->authorization = $authorization;
        $this->om = $om;
        $this->eventDispatcher = $eventDispatcher;
        $this->tokenStorage = $tokenStorage;
        $this->serializer = $serializer;
        $this->toolManager = $toolManager;
        $this->translator = $translator;
        $this->manager = $manager;
        $this->restrictionsManager = $restrictionsManager;
        $this->evaluationManager = $evaluationManager;
    }

    /**
     * @Route("/{slug}", name="claro_workspace_open")
     * @EXT\ParamConverter("user", converter="current_user", options={"allowAnonymous"=true})
     */
    public function openAction(string $slug, User $user = null, Request $request): JsonResponse
    {
        /** @var Workspace $workspace */
        $workspace = $this->om->getRepository(Workspace::class)->findOneBy(['slug' => $slug]);

        if (!$workspace) {
            throw new NotFoundHttpException('Workspace not found');
        }

        // switch to the workspace locale if needed (this is broken in UI atm)
        $this->forceWorkspaceLang($workspace, $request);
        $this->toolManager->addMissingWorkspaceTools($workspace);

        $isManager = $this->manager->isManager($workspace, $this->tokenStorage->getToken());
        $accessErrors = $this->restrictionsManager->getErrors($workspace, $user);
        if (empty($accessErrors) || $isManager) {
            $this->eventDispatcher->dispatch(new OpenWorkspaceEvent($workspace), 'workspace.open');

            // Log workspace opening
            $this->eventDispatcher->dispatch(new LogWorkspaceEnterEvent($workspace), 'log');

            // Get the list of enabled workspace tool
            if ($isManager) {
                // gets all available tools
                $orderedTools = $this->toolManager->getOrderedToolsByWorkspace($workspace);
            } else {
                // gets accessible tools by user
                $orderedTools = $this->toolManager->getOrderedToolsByWorkspace($workspace, $this->tokenStorage->getToken()->getRoleNames());
            }

            $userEvaluation = null;
            if ($user) {
                $userEvaluation = $this->serializer->serialize(
                    $this->evaluationManager->getEvaluation($workspace, $user)
                );
            }

            return new JsonResponse([
                'workspace' => $this->serializer->serialize($workspace),
                'managed' => $isManager,
                'impersonated' => $this->manager->isImpersonated($this->tokenStorage->getToken()),
                // the list of current workspace roles the user owns
                'roles' => array_map(function (Role $role) {
                    return $this->serializer->serialize($role, [Options::SERIALIZE_MINIMAL]);
                }, $this->manager->getTokenRoles($this->tokenStorage->getToken(), $workspace)),
                // append access restrictions to the loaded data if any
                // to let the manager knows that other users can not enter the workspace
                'accessErrors' => $accessErrors,
                'userEvaluation' => $userEvaluation,
                'tools' => array_values(array_map(function (OrderedTool $orderedTool) {
                    return $this->serializer->serialize($orderedTool->getTool(), [Options::SERIALIZE_MINIMAL]);
                }, $orderedTools)),
                'root' => $this->serializer->serialize($this->om->getRepository(ResourceNode::class)->findOneBy(['workspace' => $workspace, 'parent' => null]), [Options::SERIALIZE_MINIMAL]),
                // TODO : only export current user shortcuts (we get all roles for the configuration in community/editor)
                //'shortcuts' => $this->manager->getShortcuts($workspace, $this->tokenStorage->getToken()->getRoleNames()),
                'shortcuts' => array_values(array_map(function (Shortcuts $shortcuts) {
                    return $this->serializer->serialize($shortcuts);
                }, $workspace->getShortcuts()->toArray())),
            ]);
        }

        return new JsonResponse([
            'impersonated' => $this->manager->isImpersonated($this->tokenStorage->getToken()),
            'roles' => array_map(function (Role $role) {
                return $this->serializer->serialize($role, [Options::SERIALIZE_MINIMAL]);
            }, $this->manager->getTokenRoles($this->tokenStorage->getToken(), $workspace)),
            'workspace' => $this->serializer->serialize($workspace),
            'accessErrors' => $accessErrors,
        ]);
    }

    /**
     * Opens a tool.
     *
     * @Route("/{id}/tool/{toolName}", name="claro_workspace_open_tool")
     * @EXT\ParamConverter(
     *      "workspace",
     *      class="ClarolineCoreBundle:Workspace\Workspace",
     *      options={"mapping": {"id": "uuid"}}
     * )
     */
    public function openToolAction(Workspace $workspace, string $toolName): JsonResponse
    {
        $orderedTool = $this->toolManager->getOrderedTool($toolName, Tool::WORKSPACE, $workspace->getUuid());
        if (!$orderedTool) {
            throw new NotFoundHttpException(sprintf('Tool "%s" not found', $toolName));
        }

        if (!$this->authorization->isGranted('OPEN', $orderedTool)) {
            throw new AccessDeniedException();
        }

        /** @var OpenToolEvent $event */
        $event = $this->eventDispatcher->dispatch(new OpenToolEvent($workspace), 'open_tool_workspace_'.$toolName);

        $this->eventDispatcher->dispatch(new LogWorkspaceToolReadEvent($workspace, $toolName), 'log');

        return new JsonResponse(array_merge($event->getData(), [
            'data' => $this->serializer->serialize($orderedTool),
        ]));
    }

    /**
     * Submit access code.
     *
     * @Route("/unlock/{id}", name="claro_workspace_unlock", methods={"POST"})
     * @EXT\ParamConverter(
     *     "workspace",
     *     class="ClarolineCoreBundle:Workspace\Workspace",
     *     options={"mapping": {"id": "uuid"}}
     * )
     */
    public function unlockAction(Workspace $workspace, Request $request): JsonResponse
    {
        $this->restrictionsManager->unlock($workspace, json_decode($request->getContent(), true)['code']);

        return new JsonResponse(null, 204);
    }

    private function forceWorkspaceLang(Workspace $workspace, Request $request)
    {
        if ($workspace->getLang()) {
            $request->setLocale($workspace->getLang());
            //not sure if both lines are needed
            $this->translator->setLocale($workspace->getLang());
        }
    }
}
