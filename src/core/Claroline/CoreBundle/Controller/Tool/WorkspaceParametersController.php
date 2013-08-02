<?php

namespace Claroline\CoreBundle\Controller\Tool;

use Claroline\CoreBundle\Event\StrictDispatcher;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\SecurityContextInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration as EXT;
use Claroline\CoreBundle\Controller\Tool\AbstractParametersController;
use Claroline\CoreBundle\Entity\Workspace\AbstractWorkspace;
use Claroline\CoreBundle\Entity\Tool\Tool;
use Claroline\CoreBundle\Form\Factory\FormFactory;
use Claroline\CoreBundle\Manager\WorkspaceManager;
use Claroline\CoreBundle\Manager\WorkspaceTagManager;
use JMS\DiExtraBundle\Annotation as DI;

class WorkspaceParametersController extends AbstractParametersController
{
    private $workspaceManager;
    private $workspaceTagManager;
    private $security;
    private $eventDispatcher;
    private $formFactory;
    private $router;

    /**
     * @DI\InjectParams({
     *     "workspaceManager"   = @DI\Inject("claroline.manager.workspace_manager"),
     *     "workspaceTagManager"   = @DI\Inject("claroline.manager.workspace_tag_manager"),
     *     "security"           = @DI\Inject("security.context"),
     *     "eventDispatcher"    = @DI\Inject("claroline.event.event_dispatcher"),
     *     "formFactory"        = @DI\Inject("claroline.form.factory"),
     *     "router"             = @DI\Inject("router")
     * })
     */
    public function __construct(
        WorkspaceManager $workspaceManager,
        WorkspaceTagManager $workspaceTagManager,
        SecurityContextInterface $security,
        StrictDispatcher $eventDispatcher,
        FormFactory $formFactory,
        UrlGeneratorInterface $router
    )
    {
        $this->workspaceManager = $workspaceManager;
        $this->workspaceTagManager = $workspaceTagManager;
        $this->security = $security;
        $this->eventDispatcher = $eventDispatcher;
        $this->formFactory = $formFactory;
        $this->router = $router;
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
        $request = $this->getRequest();
        $form = $this->formFactory->create(FormFactory::TYPE_WORKSPACE_TEMPLATE);
        $form->handleRequest($request);

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
        $this->checkAccess($workspace);
        $form = $this->formFactory->create(FormFactory::TYPE_WORKSPACE_EDIT, array(), $workspace);

        return array(
            'form' => $form->createView(),
            'workspace' => $workspace
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
        $request = $this->getRequest();
        $form->handleRequest($request);

        if ($form->isValid()) {
            $this->workspaceManager->createWorkspace($workspace);
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
            'workspace' => $workspace
        );
    }

    /**
     * @EXT\Route(
     *     "/{workspace}/tool/{tool}/config",
     *     name="claro_workspace_tool_config"
     * )
     * @EXT\Method("GET")
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
            array($tool,$workspace)
        );

        return new Response($event->getContent());
    }
}
