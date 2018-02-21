<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Controller\Tool;

use Claroline\AppBundle\Event\StrictDispatcher;
use Claroline\CoreBundle\Entity\Tool\Tool;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Entity\Workspace\Workspace;
use Claroline\CoreBundle\Form\WorkspaceEditType;
use Claroline\CoreBundle\Library\Utilities\ClaroUtilities;
use Claroline\CoreBundle\Manager\GroupManager;
use Claroline\CoreBundle\Manager\ResourceManager;
use Claroline\CoreBundle\Manager\ToolManager;
use Claroline\CoreBundle\Manager\TransferManager;
use Claroline\CoreBundle\Manager\UserManager;
use Claroline\CoreBundle\Manager\WorkspaceManager;
use Claroline\CoreBundle\Manager\WorkspaceTagManager;
use JMS\DiExtraBundle\Annotation as DI;
use Sensio\Bundle\FrameworkExtraBundle\Configuration as EXT;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\FormFactory;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class WorkspaceParametersController extends Controller
{
    private $workspaceManager;
    private $workspaceTagManager;
    private $tokenStorage;
    private $authorization;
    private $eventDispatcher;
    private $formFactory;
    private $router;
    private $request;
    private $userManager;
    private $utilities;
    private $groupManager;
    private $toolManager;
    private $transferManager;

    /**
     * @DI\InjectParams({
     *     "workspaceManager"    = @DI\Inject("claroline.manager.workspace_manager"),
     *     "workspaceTagManager" = @DI\Inject("claroline.manager.workspace_tag_manager"),
     *     "authorization"       = @DI\Inject("security.authorization_checker"),
     *     "tokenStorage"        = @DI\Inject("security.token_storage"),
     *     "eventDispatcher"     = @DI\Inject("claroline.event.event_dispatcher"),
     *     "formFactory"         = @DI\Inject("form.factory"),
     *     "router"              = @DI\Inject("router"),
     *     "userManager"         = @DI\Inject("claroline.manager.user_manager"),
     *     "groupManager"        = @DI\Inject("claroline.manager.group_manager"),
     *     "utilities"           = @DI\Inject("claroline.utilities.misc"),
     *     "toolManager"         = @DI\Inject("claroline.manager.tool_manager"),
     *     "transferManager"     = @DI\Inject("claroline.manager.transfer_manager"),
     *     "resourceManager"     = @DI\Inject("claroline.manager.resource_manager")
     * })
     */
    public function __construct(
        WorkspaceManager $workspaceManager,
        WorkspaceTagManager $workspaceTagManager,
        ResourceManager $resourceManager,
        TransferManager $transferManager,
        TokenStorageInterface $tokenStorage,
        AuthorizationCheckerInterface $authorization,
        StrictDispatcher $eventDispatcher,
        FormFactory $formFactory,
        UrlGeneratorInterface $router,
        Request $request,
        UserManager $userManager,
        GroupManager $groupManager,
        ClaroUtilities $utilities,
        ToolManager $toolManager
    ) {
        $this->workspaceManager = $workspaceManager;
        $this->workspaceTagManager = $workspaceTagManager;
        $this->tokenStorage = $tokenStorage;
        $this->authorization = $authorization;
        $this->eventDispatcher = $eventDispatcher;
        $this->formFactory = $formFactory;
        $this->router = $router;
        $this->request = $request;
        $this->userManager = $userManager;
        $this->groupManager = $groupManager;
        $this->utilities = $utilities;
        $this->toolManager = $toolManager;
        $this->resourceManager = $resourceManager;
        $this->transferManager = $transferManager;
    }

    /**
     * @EXT\Route(
     *     "/{workspace}/editform",
     *     name="claro_workspace_edit_form"
     * )
     *
     * @EXT\Template("ClarolineCoreBundle:Tool\workspace\parameters:workspaceEdit.html.twig")
     *
     * @param Workspace $workspace
     *
     * @return Response
     */
    public function workspaceEditFormAction(Workspace $workspace)
    {
        $user = $this->tokenStorage->getToken()->getUser();
        $this->checkAccess($workspace);
        $username = is_null($workspace->getCreator()) ? '' : $workspace->getCreator()->getUsername();
        $creationDate = is_null($workspace->getCreationDate()) ? null : $this->utilities->intlDateFormat(
            $workspace->getCreationDate()
        );
        $expDate = is_null($workspace->getEndDate()) ? null : $this->utilities->intlDateFormat(
            $workspace->getEndDate()
        );
        $startDate = is_null($workspace->getEndDate()) ? null : $this->utilities->intlDateFormat(
            $workspace->getEndDate()
        );
        $count = $this->workspaceManager->countUsers($workspace, true);
        $storageUsed = $this->workspaceManager->getUsedStorage($workspace);
        $storageUsed = $this->utilities->formatFileSize($storageUsed);
        $countResources = $this->workspaceManager->countResources($workspace);
        $workspaceAdminTool = $this->toolManager->getAdminToolByName('workspace_management');
        $isAdmin = $this->authorization->isGranted('OPEN', $workspaceAdminTool);

        $workspaceType = new WorkspaceEditType(
            $username,
            $creationDate,
            $count,
            $storageUsed,
            $countResources,
            $isAdmin,
            $expDate,
            $startDate
        );

        $form = $this->formFactory->create($workspaceType, $workspace);

        if ($workspace->getSelfRegistration()) {
            $url = $this->router->generate(
                'claro_workspace_subscription_url_generate',
                ['workspace' => $workspace->getId()],
                true
            );
        } else {
            $url = '';
        }

        return [
            'form' => $form->createView(),
            'workspace' => $workspace,
            'url' => $url,
            'user' => $user,
            'count' => $count,
        ];
    }

    /**
     * @EXT\Route(
     *     "/{workspace}/edit",
     *     name="claro_workspace_edit"
     * )
     * @EXT\Method("POST")
     *
     * @EXT\Template("ClarolineCoreBundle:Tool\workspace\parameters:workspaceEdit.html.twig")
     *
     * @param Workspace $workspace
     *
     * @return Response
     */
    public function workspaceEditAction(Workspace $workspace)
    {
        if (!$this->authorization->isGranted('parameters', $workspace)) {
            throw new AccessDeniedException();
        }

        $wsRegisteredName = $workspace->getName();
        $wsRegisteredDisplayable = $workspace->isDisplayable();
        $workspaceAdminTool = $this->toolManager->getAdminToolByName('workspace_management');
        $isAdmin = $this->authorization->isGranted('OPEN', $workspaceAdminTool);
        $expDate = is_null($workspace->getCreationDate()) ? null : $this->utilities->intlDateFormat(
            $workspace->getEndDate()
        );

        $workspaceType = new WorkspaceEditType(null, null, null, null, null, $isAdmin, $expDate);
        $form = $this->formFactory->create($workspaceType, $workspace);
        $form->handleRequest($this->request);

        if ($form->isValid()) {
            $this->workspaceManager->editWorkspace($workspace);
            $this->workspaceManager->rename($workspace, $workspace->getName());
            $displayable = $workspace->isDisplayable();

            if (!$displayable && $displayable !== $wsRegisteredDisplayable) {
                $this->workspaceTagManager->deleteAllAdminRelationsFromWorkspace($workspace);
            }

            return $this->redirect(
                $this->generateUrl(
                    'claro_workspace_open_tool',
                    [
                        'workspaceId' => $workspace->getId(),
                        'toolName' => 'parameters',
                    ]
                )
            );
        } else {
            $workspace->setName($wsRegisteredName);
        }

        $user = $this->tokenStorage->getToken()->getUser();

        if ($workspace->getSelfRegistration()) {
            $url = $this->router->generate(
                'claro_workspace_subscription_url_generate',
                ['workspace' => $workspace->getId()],
                true
            );
        } else {
            $url = '';
        }

        return [
            'form' => $form->createView(),
            'workspace' => $workspace,
            'url' => $url,
            'user' => $user,
        ];
    }

    /**
     * @EXT\Route(
     *     "/{workspace}/tool/{tool}/config",
     *     name="claro_workspace_tool_config"
     * )
     *
     * @param Workspace $workspace
     * @param Tool      $tool
     *
     * @return Response
     */
    public function openWorkspaceToolConfig(Workspace $workspace, Tool $tool)
    {
        $this->checkAccess($workspace);
        $event = $this->eventDispatcher->dispatch(
            strtolower('configure_workspace_tool_'.$tool->getName()),
            'ConfigureWorkspaceTool',
            [$tool, $workspace]
        );

        return new Response($event->getContent());
    }

    /**
     * @EXT\Route(
     *     "/{workspace}/subscription/url/generate",
     *     name="claro_workspace_subscription_url_generate"
     * )
     *
     * @EXT\Template("ClarolineCoreBundle:Tool\workspace\parameters:generate_url_subscription.html.twig")
     *
     * @param Workspace $workspace
     *
     * @return Response
     */
    public function urlSubscriptionGenerateAction(Workspace $workspace)
    {
        $user = $this->tokenStorage->getToken()->getUser();

        if ('anon.' === $user) {
            return $this->redirect(
                $this->generateUrl(
                    'claro_workspace_subscription_url_generate_anonymous',
                    [
                        'workspace' => $workspace->getId(),
                        'toolName' => 'home',
                    ]
                )
            );
        } else {
            return $this->redirect(
                $this->generateUrl(
                    'claro_workspace_subscription_url_generate_user',
                    [
                        'workspace' => $workspace->getId(),
                    ]
                )
            );
        }
    }

    /**
     * @EXT\Route(
     *     "/{workspace}/subscription/url/generate/anonymous",
     *     name="claro_workspace_subscription_url_generate_anonymous"
     * )
     *
     * @EXT\Template("ClarolineCoreBundle:Tool\workspace\parameters:generate_url_subscription_anonymous.html.twig")
     *
     * @param Request   $request
     * @param Workspace $workspace
     *
     * @return Response
     */
    public function anonymousSubscriptionAction(Request $request, Workspace $workspace)
    {
        if (!$workspace->getSelfRegistration()) {
            throw new AccessDeniedHttpException();
        }

        $form = $this->get('claroline.manager.registration_manager')->getRegistrationForm(new User());
        $form->handleRequest($this->request);

        if ($form->isValid()) {
            $user = $form->getData();
            $this->userManager->createUser($user);
            if ($workspace->getRegistrationValidation()) {
                $this->workspaceManager->addUserQueue($workspace, $user);
                $flashBag = $request->getSession()->getFlashBag();
                $translator = $this->get('translator');
                $flashBag->set('warning', $translator->trans('account_created_awaiting_validation', [], 'platform'));
            } else {
                $this->workspaceManager->addUserAction($workspace, $user);
            }

            return $this->redirect(
                $this->generateUrl(
                    'claro_workspace_open',
                    ['workspaceId' => $workspace->getId()]
                )
            );
        }

        return [
            'form' => $form->createView(),
            'workspace' => $workspace,
        ];
    }

    /**
     * @EXT\Route(
     *     "/user/subscribe/workspace/{workspace}",
     *     name="claro_workspace_subscription_url_generate_user"
     * )
     *
     * @EXT\Template("ClarolineCoreBundle:Tool\workspace\parameters:url_subscription_user_login.html.twig")
     *
     * @param Workspace $workspace
     * @param Request   $request
     *
     * @throws \Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException
     */
    public function userSubscriptionAction(Workspace $workspace, Request $request)
    {
        if (!$this->isGranted('IS_AUTHENTICATED_FULLY')) {
            throw new AccessDeniedException();
        }

        $user = $this->get('security.token_storage')->getToken()->getUser();

        // If user is admin or registration validation is disabled, subscribe user
        if ($this->isGranted('ROLE_ADMIN') || !$workspace->getRegistrationValidation()) {
            $this->workspaceManager->addUserAction($workspace, $user);

            return $this->redirect(
                $this->generateUrl(
                    'claro_workspace_open',
                    ['workspaceId' => $workspace->getId()]
                )
            );
        }
        // Otherwise add user to validation queue if not already there
        if (!$this->workspaceManager->isUserInValidationQueue($workspace, $user)) {
            $this->workspaceManager->addUserQueue($workspace, $user);
        }

        $flashBag = $request->getSession()->getFlashBag();
        $translator = $this->get('translator');
        $flashBag->set('warning', $translator->trans('workspace_awaiting_validation', [], 'platform'));

        return $this->redirect($this->generateUrl('claro_desktop_open'));
    }

    private function checkAccess(Workspace $workspace)
    {
        if (!$this->authorization->isGranted('parameters', $workspace)) {
            throw new AccessDeniedException();
        }
    }
}
