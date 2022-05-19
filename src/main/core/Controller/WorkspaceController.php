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
use Claroline\AppBundle\Event\StrictDispatcher;
use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Entity\Resource\ResourceNode;
use Claroline\CoreBundle\Entity\Role;
use Claroline\CoreBundle\Entity\Tool\AbstractTool;
use Claroline\CoreBundle\Entity\Tool\OrderedTool;
use Claroline\CoreBundle\Entity\Tool\Tool;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Entity\Workspace\Shortcuts;
use Claroline\CoreBundle\Entity\Workspace\Workspace;
use Claroline\CoreBundle\Event\CatalogEvents\ToolEvents;
use Claroline\CoreBundle\Event\CatalogEvents\WorkspaceEvents;
use Claroline\CoreBundle\Event\Log\LogWorkspaceEnterEvent;
use Claroline\CoreBundle\Event\Tool\OpenToolEvent;
use Claroline\CoreBundle\Event\Workspace\OpenWorkspaceEvent;
use Claroline\CoreBundle\Manager\Tool\ToolManager;
use Claroline\CoreBundle\Manager\Workspace\WorkspaceManager;
use Claroline\CoreBundle\Manager\Workspace\WorkspaceRestrictionsManager;
use Claroline\EvaluationBundle\Manager\WorkspaceEvaluationManager;
use Sensio\Bundle\FrameworkExtraBundle\Configuration as EXT;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * @Route("/workspaces", options={"expose" = true})
 */
class WorkspaceController
{
    /** @var AuthorizationCheckerInterface */
    private $authorization;
    /** @var ObjectManager */
    private $om;
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
    /** @var WorkspaceEvaluationManager */
    private $evaluationManager;
    /** @var StrictDispatcher */
    private $strictDispatcher;

    public function __construct(
        AuthorizationCheckerInterface $authorization,
        ObjectManager $om,
        TokenStorageInterface $tokenStorage,
        SerializerProvider $serializer,
        ToolManager $toolManager,
        TranslatorInterface $translator,
        WorkspaceManager $manager,
        WorkspaceRestrictionsManager $restrictionsManager,
        WorkspaceEvaluationManager $evaluationManager,
        StrictDispatcher $strictDispatcher
    ) {
        $this->authorization = $authorization;
        $this->om = $om;
        $this->tokenStorage = $tokenStorage;
        $this->serializer = $serializer;
        $this->toolManager = $toolManager;
        $this->translator = $translator;
        $this->manager = $manager;
        $this->restrictionsManager = $restrictionsManager;
        $this->evaluationManager = $evaluationManager;
        $this->strictDispatcher = $strictDispatcher;
    }

    /**
     * @Route("/{slug}", name="claro_workspace_open")
     * @EXT\ParamConverter("user", converter="current_user", options={"allowAnonymous"=true})
     */
    public function openAction(string $slug, Request $request, ?User $user = null): JsonResponse
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
            $this->strictDispatcher->dispatch(
                WorkspaceEvents::OPEN,
                OpenWorkspaceEvent::class,
                [$workspace]
            );

            // Log workspace opening
            $this->strictDispatcher->dispatch(
                'log',
                LogWorkspaceEnterEvent::class,
                [$workspace]
            );

            $userEvaluation = null;
            if ($user) {
                $userEvaluation = $this->serializer->serialize(
                    $this->evaluationManager->getUserEvaluation($workspace, $user),
                    [Options::SERIALIZE_MINIMAL]
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
                // get the list of enabled workspace tool
                'tools' => array_values(array_map(function (OrderedTool $orderedTool) {
                    return $this->serializer->serialize($orderedTool, [Options::SERIALIZE_MINIMAL]);
                }, $this->toolManager->getOrderedToolsByWorkspace($workspace))),
                'root' => $this->serializer->serialize($this->om->getRepository(ResourceNode::class)->findOneBy(['workspace' => $workspace, 'parent' => null]), [Options::SERIALIZE_MINIMAL]),
                // TODO : only export current user shortcuts (we get all roles for the configuration in community/editor)
                //'shortcuts' => $this->manager->getShortcuts($workspace, $this->tokenStorage->getToken()->getRoleNames()),
                'shortcuts' => array_values(array_map(function (Shortcuts $shortcuts) {
                    return $this->serializer->serialize($shortcuts);
                }, $workspace->getShortcuts()->toArray())),
            ]);
        }

        $statusCode = 403;
        if (!$workspace->getSelfRegistration() && !$this->authorization->isGranted('IS_AUTHENTICATED_FULLY') && !$this->restrictionsManager->hasRights($workspace)) {
            // let the API handles the access error
            $statusCode = 401;
        }

        // return the details of access errors to display it to users
        return new JsonResponse([
            'impersonated' => $this->manager->isImpersonated($this->tokenStorage->getToken()),
            'roles' => array_map(function (Role $role) {
                return $this->serializer->serialize($role, [Options::SERIALIZE_MINIMAL]);
            }, $this->manager->getTokenRoles($this->tokenStorage->getToken(), $workspace)),
            'workspace' => $this->serializer->serialize($workspace),
            'accessErrors' => $accessErrors,
        ], $statusCode);
    }

    /**
     * Opens a tool.
     *
     * @Route("/{id}/tool/{toolName}", name="claro_workspace_open_tool")
     * @EXT\ParamConverter(
     *      "workspace",
     *      class="Claroline\CoreBundle\Entity\Workspace\Workspace",
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

        $currentUser = $this->tokenStorage->getToken()->getUser();
        $eventParams = [
            $workspace,
            $currentUser instanceof User ? $currentUser : null,
            AbstractTool::WORKSPACE,
            $toolName,
        ];

        $this->strictDispatcher->dispatch(
            ToolEvents::OPEN,
            OpenToolEvent::class,
            $eventParams
        );

        /** @var OpenToolEvent $event */
        $event = $this->strictDispatcher->dispatch(
            ToolEvents::getEventName(ToolEvents::OPEN, AbstractTool::WORKSPACE, $toolName),
            OpenToolEvent::class,
            $eventParams
        );

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
     *     class="Claroline\CoreBundle\Entity\Workspace\Workspace",
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
