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

use Claroline\CoreBundle\Event\StrictDispatcher;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\SecurityContextInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration as EXT;
use Claroline\CoreBundle\Entity\Workspace\AbstractWorkspace;
use Claroline\CoreBundle\Entity\Tool\Tool;
use Claroline\CoreBundle\Form\Factory\FormFactory;
use Claroline\CoreBundle\Manager\WorkspaceManager;
use Claroline\CoreBundle\Manager\WorkspaceTagManager;
use Claroline\CoreBundle\Manager\LocaleManager;
use Claroline\CoreBundle\Manager\UserManager;
use Claroline\CoreBundle\Manager\TermsOfServiceManager;
use JMS\DiExtraBundle\Annotation as DI;
use Claroline\CoreBundle\Library\Utilities\ClaroUtilities;

class WorkspaceParametersController extends Controller
{
    private $workspaceManager;
    private $workspaceTagManager;
    private $security;
    private $eventDispatcher;
    private $formFactory;
    private $router;
    private $request;
    private $localeManager;
    private $userManager;
    private $tosManager;
    private $utilities;

    /**
     * @DI\InjectParams({
     *     "workspaceManager"    = @DI\Inject("claroline.manager.workspace_manager"),
     *     "workspaceTagManager" = @DI\Inject("claroline.manager.workspace_tag_manager"),
     *     "security"            = @DI\Inject("security.context"),
     *     "eventDispatcher"     = @DI\Inject("claroline.event.event_dispatcher"),
     *     "formFactory"         = @DI\Inject("claroline.form.factory"),
     *     "router"              = @DI\Inject("router"),
     *     "localeManager"       = @DI\Inject("claroline.common.locale_manager"),
     *     "userManager"         = @DI\Inject("claroline.manager.user_manager"),
     *     "tosManager"          = @DI\Inject("claroline.common.terms_of_service_manager"),
     *      "utilities"          = @DI\Inject("claroline.utilities.misc")
     * })
     */
    public function __construct(
        WorkspaceManager $workspaceManager,
        WorkspaceTagManager $workspaceTagManager,
        SecurityContextInterface $security,
        StrictDispatcher $eventDispatcher,
        FormFactory $formFactory,
        UrlGeneratorInterface $router,
        Request $request,
        LocaleManager $localeManager,
        UserManager $userManager,
        TermsOfServiceManager $tosManager,
        ClaroUtilities $utilities
    )
    {
        $this->workspaceManager = $workspaceManager;
        $this->workspaceTagManager = $workspaceTagManager;
        $this->security = $security;
        $this->eventDispatcher = $eventDispatcher;
        $this->formFactory = $formFactory;
        $this->router = $router;
        $this->request = $request;
        $this->localeManager = $localeManager;
        $this->userManager = $userManager;
        $this->tosManager = $tosManager;
        $this->utilities = $utilities;
    }

    /**
     * @EXT\Route(
     *     "/{workspace}/form/export",
     *     name="claro_workspace_export_form"
     * )
     * @EXT\Method("GET")
     *
     * @EXT\Template("ClarolineCoreBundle:Tool\workspace\parameters:template.html.twig")
     *
     * @param AbstractWorkspace $workspace
     *
     * @return Response
     */
    public function workspaceExportFormAction(AbstractWorkspace $workspace)
    {
        $this->checkAccess($workspace);
        $form = $this->formFactory->create(FormFactory::TYPE_WORKSPACE_TEMPLATE);

        return array(
            'form' => $form->createView(),
            'workspace' => $workspace
        );
    }

    /**
     * @EXT\Route(
     *     "/{workspace}/export",
     *     name="claro_workspace_export"
     * )
     * @EXT\Method("POST")
     *
     * @EXT\Template("ClarolineCoreBundle:Tool\workspace\parameters:template.html.twig")
     *
     * @param AbstractWorkspace $workspace
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function workspaceExportAction(AbstractWorkspace $workspace)
    {
        $this->checkAccess($workspace);
        $form = $this->formFactory->create(FormFactory::TYPE_WORKSPACE_TEMPLATE);
        $form->handleRequest($this->request);

        if ($form->isValid()) {
            $name = $form->get('name')->getData();
            $this->workspaceManager->export($workspace, $name);
            $route = $this->router->generate(
                'claro_workspace_open_tool',
                array('toolName' => 'parameters', 'workspaceId' => $workspace->getId())
            );

            return new RedirectResponse($route);
        }

        return array(
            'form' => $form->createView(),
            'workspace' => $workspace
        );
    }

    /**
     * @EXT\Route(
     *     "/{workspace}/editform",
     *     name="claro_workspace_edit_form"
     * )
     * @EXT\Method("GET")
     *
     * @EXT\Template("ClarolineCoreBundle:Tool\workspace\parameters:workspaceEdit.html.twig")
     *
     * @param AbstractWorkspace $workspace
     *
     * @return Response
     */
    public function workspaceEditFormAction(AbstractWorkspace $workspace)
    {
        $user = $this->security->getToken()->getUser();
        $this->checkAccess($workspace);
        $username = is_null( $workspace->getCreator()) ? '' : $workspace->getCreator()->getUsername(); 
        $creationDate = is_null(
                            $workspace->getCreationDate()) ? 
                            null : $this->utilities->intlDateFormat($workspace->getCreationDate());
        $count = $this->workspaceManager->countUsers($workspace->getId());
        $form = $this->formFactory->create(FormFactory::TYPE_WORKSPACE_EDIT, array($username, $creationDate, $count), $workspace);
        
        if ($workspace->getSelfRegistration()) {
            $url = $this->router->generate(
                'claro_workspace_subscription_url_generate',
                array('workspace' => $workspace->getId()),
                true
            );
        } else {
            $url = '';
        }

        return array(
            'form' => $form->createView(),
            'workspace' => $workspace,
            'url' => $url,
            'user' => $user,
            'count' => $count
        );
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
     * @param AbstractWorkspace $workspace
     *
     * @return Response
     */
    public function workspaceEditAction(AbstractWorkspace $workspace)
    {
        if (!$this->security->isGranted('parameters', $workspace)) {
            throw new AccessDeniedException();
        }

        $wsRegisteredName = $workspace->getName();
        $wsRegisteredCode = $workspace->getCode();
        $wsRegisteredDisplayable = $workspace->isDisplayable();
        $form = $this->formFactory->create(FormFactory::TYPE_WORKSPACE_EDIT, array(), $workspace);
        $form->handleRequest($this->request);

        if ($form->isValid()) {
            $this->workspaceManager->createWorkspace($workspace);
            $this->workspaceManager->rename($workspace, $workspace->getName());
            $displayable = $workspace->isDisplayable();

            if (!$displayable && $displayable !== $wsRegisteredDisplayable) {
                $this->workspaceTagManager->deleteAllAdminRelationsFromWorkspace($workspace);
            }

            return $this->redirect(
                $this->generateUrl(
                    'claro_workspace_open_tool',
                    array(
                        'workspaceId' => $workspace->getId(),
                        'toolName' => 'parameters'
                    )
                )
            );
        } else {
            $workspace->setName($wsRegisteredName);
            $workspace->setCode($wsRegisteredCode);
        }

        return array(
            'form' => $form->createView(),
            'workspace' => $workspace,
        );
    }

    /**
     * @EXT\Route(
     *     "/{workspace}/tool/{tool}/config",
     *     name="claro_workspace_tool_config"
     * )
     *
     * @param AbstractWorkspace $workspace
     * @param Tool              $tool
     *
     * @return Response
     */
    public function openWorkspaceToolConfig(AbstractWorkspace $workspace, Tool $tool)
    {
        $this->checkAccess($workspace);
        $event = $this->eventDispatcher->dispatch(
            strtolower('configure_workspace_tool_' . $tool->getName()),
            'ConfigureWorkspaceTool',
            array($tool, $workspace)
        );

        return new Response($event->getContent());
    }

    /**
     * @EXT\Route(
     *     "/{workspace}/subscription/url/generate",
     *     name="claro_workspace_subscription_url_generate"
     * )
     * @EXT\Method("GET")
     *
     * @EXT\Template("ClarolineCoreBundle:Tool\workspace\parameters:generate_url_subscription.html.twig")
     *
     * @param AbstractWorkspace $workspace
     *
     * @return Response
     */
    public function urlSubscriptionGenerateAction(AbstractWorkspace $workspace)
    {
        $user = $this->security->getToken()->getUser();

        if ( $user === 'anon.') {
            return $this->redirect(
                $this->generateUrl(
                    'claro_workspace_subscription_url_generate_anonymous',
                    array(
                        'workspace' => $workspace->getId(),
                        'toolName' => 'home'
                    )
                )
            );
        }

        $this->workspaceManager->addUserAction($workspace, $user);

        return $this->redirect(
            $this->generateUrl('claro_workspace_open_tool', array('workspaceId' => $workspace->getId(), 'toolName' => 'home'))
        );
    }

    /**
     * @EXT\Route(
     *     "/{workspace}/subscription/url/generate/anonymous",
     *     name="claro_workspace_subscription_url_generate_anonymous"
     * )
     * @EXT\Method({"GET","POST"})
     *
     * @EXT\Template("ClarolineCoreBundle:Tool\workspace\parameters:generate_url_subscription_anonymous.html.twig")
     *
     * @param AbstractWorkspace $workspace
     *
     * @throws \Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException
     * @return Response
     */
    public function anonymousSubscriptionAction(AbstractWorkspace $workspace)
    {
        if (!$workspace->getSelfRegistration()) {
            throw new AccessDeniedHttpException();
        }

        $form = $this->formFactory->create(
            FormFactory::TYPE_USER_BASE_PROFILE, array($this->localeManager, $this->tosManager)
        );
        $form->handleRequest($this->request);

        if ($form->isValid()) {
            $user = $form->getData();
            $this->userManager->createUser($user);
            $this->workspaceManager->addUserAction($workspace, $user);
            return $this->redirect(
                $this->generateUrl('claro_workspace_open_tool', array('workspaceId' => $workspace->getId(), 'toolName' => 'home')));
        }

        return array(
            'form' => $form->createView(),
            'workspace' => $workspace
        );
    }

    private function checkAccess(AbstractWorkspace $workspace)
    {
        if (!$this->security->isGranted('parameters', $workspace)) {
            throw new AccessDeniedException();
        }
    }
}
