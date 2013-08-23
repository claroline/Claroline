<?php

namespace Claroline\CoreBundle\Controller\Tool;

use Claroline\CoreBundle\Entity\Home\HomeTab;
use Claroline\CoreBundle\Entity\Widget\DisplayConfig;
use Claroline\CoreBundle\Entity\Widget\Widget;
use Claroline\CoreBundle\Entity\Workspace\AbstractWorkspace;
use Claroline\CoreBundle\Event\StrictDispatcher;
use Claroline\CoreBundle\Form\Factory\FormFactory;
use Claroline\CoreBundle\Library\Widget\Manager;
use Claroline\CoreBundle\Manager\HomeTabManager;
use Claroline\CoreBundle\Manager\ToolManager;
use Doctrine\ORM\EntityManager;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Security\Core\SecurityContextInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration as EXT;
use JMS\DiExtraBundle\Annotation as DI;

/**
 * Controller of the workspace/desktop home page.
 */
class HomeController extends Controller
{
    private $em;
    private $eventDispatcher;
    private $formFactory;
    private $homeTabManager;
    private $request;
    private $securityContext;
    private $toolManager;
    private $widgetManager;

    /**
     * @DI\InjectParams({
     *     "em"                 = @DI\Inject("doctrine.orm.entity_manager"),
     *     "eventDispatcher"    = @DI\Inject("claroline.event.event_dispatcher"),
     *     "formFactory"        = @DI\Inject("claroline.form.factory"),
     *     "homeTabManager"     = @DI\Inject("claroline.manager.home_tab_manager"),
     *     "request"            = @DI\Inject("request"),
     *     "securityContext"    = @DI\Inject("security.context"),
     *     "toolManager"        = @DI\Inject("claroline.manager.tool_manager"),
     *     "widgetManager"      = @DI\Inject("claroline.widget.manager")
     * })
     */
    public function __construct(
        EntityManager $em,
        StrictDispatcher $eventDispatcher,
        FormFactory $formFactory,
        HomeTabManager $homeTabManager,
        Request $request,
        SecurityContextInterface $securityContext,
        ToolManager $toolManager,
        Manager $widgetManager
    )
    {
        $this->em = $em;
        $this->eventDispatcher = $eventDispatcher;
        $this->formFactory = $formFactory;
        $this->homeTabManager = $homeTabManager;
        $this->request = $request;
        $this->securityContext = $securityContext;
        $this->toolManager = $toolManager;
        $this->widgetManager = $widgetManager;
    }

    /**
     * @EXT\Route(
     *     "/perso",
     *     name="claro_tool_desktop_perso"
     * )
     *
     * @EXT\Template("ClarolineCoreBundle:Tool\desktop\home:perso.html.twig")
     *
     * Displays the Perso desktop tab.
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function persoAction()
    {
        return array();
    }

    /**
     * @EXT\Route(
     *     "/info",
     *     name="claro_tool_desktop_info"
     * )
     *
     * @EXT\Template("ClarolineCoreBundle:Tool\desktop\home:info.html.twig")
     *
     * Displays the Info desktop tab.
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function infoAction()
    {
        $user = $this->securityContext->getToken()->getUser();
        $homeTabs = $this->homeTabManager->getDesktopHomeTabsByUser($user);

        return array('homeTabs' => $homeTabs);
    }

    /**
     * @EXT\Route(
     *     "workspace/{workspace}/widget",
     *     name="claro_workspace_widget_properties"
     * )
     * @EXT\Method("GET")
     *
     * @EXT\Template("ClarolineCoreBundle:Tool\workspace\home:widgetProperties.html.twig")
     *
     * Renders the workspace widget properties page.
     *
     * @param AbstractWorkspace $workspace
     *
     * @return Response
     */
    public function workspaceWidgetsPropertiesAction(AbstractWorkspace $workspace)
    {
        if (!$this->securityContext->isGranted('parameters', $workspace)) {
            throw new AccessDeniedException();
        }

        $configs = $this->widgetManager
            ->generateWorkspaceDisplayConfig($workspace->getId());

        return array(
            'workspace' => $workspace,
            'configs' => $configs,
            'tool' => $this->getHomeTool()
        );
    }

    /**
     * @EXT\Route(
     *     "workspace/{workspace}/widget/{widget}/baseconfig/{adminConfig}/invertvisible",
     *     name="claro_workspace_widget_invertvisible",
     *     options={"expose"=true}
     * )
     * @EXT\Method("POST")
     *
     * Inverts the visibility boolean of a widget in the specified workspace.
     * If the DisplayConfig entity for the workspace doesn't exist in the database
     * yet, it's created here.
     *
     * @param AbstractWorkspace workspace
     * @param Widget        $widget
     * @param DisplayConfig $adminConfig The displayConfig defined by the administrator: it's the
     * configuration entity for widgets
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function workspaceInvertVisibleWidgetAction(
        AbstractWorkspace $workspace,
        Widget $widget,
        DisplayConfig $adminConfig
    )
    {
        if (!$this->securityContext->isGranted('parameters', $workspace)) {
            throw new AccessDeniedException();
        }

        $displayConfig = $this->em
            ->getRepository('ClarolineCoreBundle:Widget\DisplayConfig')
            ->findOneBy(array('workspace' => $workspace, 'widget' => $widget));

        if ($displayConfig === null) {
            $displayConfig = new DisplayConfig();
            $displayConfig->setParent($adminConfig);
            $displayConfig->setWidget($widget);
            $displayConfig->setWorkspace($workspace);
            $displayConfig->setVisible($adminConfig->isVisible());
            $displayConfig->setLock(true);
            $displayConfig->setDesktop(false);
        }

        $displayConfig->invertVisible();
        $this->em->persist($displayConfig);
        $this->em->flush();

        return new Response('success');
    }

    /**
     * @EXT\Route(
     *     "/{workspace}/widget/{widget}/configuration",
     *     name="claro_workspace_widget_configuration",
     *     options={"expose"=true}
     * )
     * @EXT\Method("GET")
     *
     * Asks a widget to render its configuration page for a workspace.
     *
     * @param AbstractWorkspace $workspace
     * @param Widget            $widget
     *
     * @return Response
     */
    public function workspaceConfigureWidgetAction(AbstractWorkspace $workspace, Widget $widget)
    {
        if (!$this->securityContext->isGranted('parameters', $workspace)) {
            throw new AccessDeniedException();
        }

        $event = $this->eventDispatcher->dispatch(
            "widget_{$widget->getName()}_configuration_workspace",
            'ConfigureWidgetWorkspace',
            array($workspace)
        );

        if ($event->getContent() !== '') {
            if ($this->request->isXMLHttpRequest()) {
                return $this->render(
                    'ClarolineCoreBundle:Tool\workspace\home:widgetConfigurationForm.html.twig',
                    array(
                        'content' => $event->getContent(),
                        'workspace' => $workspace,
                        'tool' => $this->getHomeTool()
                    )
                );
            }

            return $this->render(
                'ClarolineCoreBundle:Tool\workspace\home:widgetConfiguration.html.twig',
                array(
                    'content' => $event->getContent(),
                    'workspace' => $workspace,
                    'tool' => $this->getHomeTool()
                )
            );
        }

    }

    /**
     * @EXT\Route(
     *     "desktop/widget/properties",
     *     name="claro_desktop_widget_properties"
     * )
     *
     * @EXT\Template("ClarolineCoreBundle:Tool\desktop\home:widgetProperties.html.twig")
     *
     * Displays the widget configuration page.
     *
     * @return Response
     */
    public function desktopWidgetPropertiesAction()
    {
        $user = $this->securityContext->getToken()->getUser();
        $configs = $this->widgetManager
            ->generateDesktopDisplayConfig($user->getId());

        return array(
            'configs' => $configs,
            'user' => $user,
            'tool' => $this->getHomeTool()
        );
    }

    /**
     * @EXT\Route(
     *     "desktop/config/{adminConfig}/widget/{widget}/invertvisible",
     *     name="claro_desktop_widget_invertvisible",
     *     options={"expose"=true}
     * )
     * @EXT\Method("POST")
     *
     * Inverts the visibility boolean for a widget for the current user.
     *
     * @param Widget        $widget      the widget
     * @param DisplayConfig $adminConfig the display config (the configuration entity for widgets)
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function desktopInvertVisibleUserWidgetAction(Widget $widget, DisplayConfig $adminConfig)
    {
        $user = $this->securityContext->getToken()->getUser();
        $displayConfig = $this->em->getRepository('ClarolineCoreBundle:Widget\DisplayConfig')
            ->findOneBy(array('user' => $user, 'widget' => $widget));

        if ($displayConfig === null) {
            $displayConfig = new DisplayConfig();
            $displayConfig->setParent($adminConfig);
            $displayConfig->setWidget($widget);
            $displayConfig->setUser($user);
            $displayConfig->setVisible($adminConfig->isVisible());
            $displayConfig->setLock(true);
            $displayConfig->setDesktop(true);
        }

        $displayConfig->invertVisible();
        $this->em->persist($displayConfig);
        $this->em->flush();

        return new Response('success');
    }

    /**
     * @EXT\Route(
     *     "desktop/widget/{widget}/configuration/desktop",
     *     name="claro_desktop_widget_configuration",
     *     options={"expose"=true}
     * )
     * @EXT\Method("GET")
     *
     * Asks a widget to display its configuration page.
     *
     * @param Widget $widget the widget
     *
     * @return Response
     */
    public function desktopConfigureWidgetAction(Widget $widget)
    {
        $user = $this->securityContext->getToken()->getUser();
        $event = $this->eventDispatcher->dispatch(
            "widget_{$widget->getName()}_configuration_desktop",
            'ConfigureWidgetDesktop',
            array($user)
        );

        if ($event->getContent() !== '') {
            if ($this->request->isXMLHttpRequest()) {
                return $this->render(
                    'ClarolineCoreBundle:Tool\desktop\home:widgetConfigurationForm.html.twig',
                    array('content' => $event->getContent(), 'tool' => $this->getHomeTool())
                );
            }

            return $this->render(
                'ClarolineCoreBundle:Tool\desktop\home:widgetConfiguration.html.twig',
                array('content' => $event->getContent(), 'tool' => $this->getHomeTool())
            );
        }
    }

    /**
     * @EXT\Route(
     *     "desktop/home_tab/properties",
     *     name="claro_desktop_home_tab_properties",
     *     options = {"expose"=true}
     * )
     * @EXT\Method("GET")
     * @EXT\Template("ClarolineCoreBundle:Tool\desktop\home:homeTabProperties.html.twig")
     *
     * Displays the homeTab configuration page.
     *
     * @return Response
     */
    public function desktopHomeTabPropertiesAction()
    {
        $this->checkAccess();

        $user = $this->securityContext->getToken()->getUser();
        $homeTabs = $this->homeTabManager->getDesktopHomeTabsByUser($user);

        return array(
            'homeTabs' => $homeTabs,
            'tool' => $this->getHomeTool()
        );
    }

    /**
     * @EXT\Route(
     *     "desktop/user/home_tab/create/form",
     *     name="claro_user_desktop_home_tab_create_form"
     * )
     * @EXT\Method("GET")
     * @EXT\Template("ClarolineCoreBundle:Tool\desktop\home:userDesktopHomeTabCreateForm.html.twig")
     *
     * Displays the homeTab form.
     *
     * @return Response
     */
    public function userDesktopHomeTabCreateFormAction()
    {
        $this->checkAccess();

        $homeTab = new HomeTab();
        $form = $this->formFactory->create(FormFactory::TYPE_HOME_TAB, array(), $homeTab);

        return array(
            'form' => $form->createView(),
            'tool' => $this->getHomeTool()
        );
    }

    /**
     * @EXT\Route(
     *     "desktop/user/home_tab/create",
     *     name="claro_user_desktop_home_tab_create"
     * )
     * @EXT\Method("POST")
     * @EXT\Template("ClarolineCoreBundle:Tool\desktop\home:userDesktopHomeTabCreateForm.html.twig")
     *
     * Create a new homeTab.
     *
     * @return Response
     */
    public function userDesktopHomeTabCreateAction()
    {
        $this->checkAccess();

        $user = $this->securityContext->getToken()->getUser();
        $homeTab = new HomeTab();

        $form = $this->formFactory->create(FormFactory::TYPE_HOME_TAB, array(), $homeTab);
        $request = $this->getRequest();
        $form->handleRequest($request);

        if ($form->isValid()) {
            $homeTab->setType('desktop');
            $homeTab->setUser($user);
            $lastOrder = $this->homeTabManager->getOrderOfLastDesktopHomeTabByUser($user);

            if (is_null($lastOrder['order_max'])) {
                $homeTab->setTabOrder(1);
            }
            else {
                $homeTab->setTabOrder($lastOrder['order_max'] + 1);
            }
            $this->homeTabManager->insertHomeTab($homeTab);

            return $this->redirect(
                $this->generateUrl('claro_desktop_home_tab_properties')
            );
        }

        return array(
            'form' => $form->createView(),
            'tool' => $this->getHomeTool()
        );
    }

    /**
     * @EXT\Route(
     *     "desktop/user/home_tab/{homeTabId}/edit/form",
     *     name="claro_user_desktop_home_tab_edit_form"
     * )
     * @EXT\Method("GET")
     * @EXT\ParamConverter(
     *     "homeTab",
     *     class="ClarolineCoreBundle:Home\HomeTab",
     *     options={"id" = "homeTabId", "strictId" = true}
     * )
     * @EXT\Template("ClarolineCoreBundle:Tool\desktop\home:userDesktopHomeTabEditForm.html.twig")
     *
     * Displays the homeTab edition form.
     *
     * @return Response
     */
    public function userDesktopHomeTabEditFormAction(HomeTab $homeTab)
    {
        $this->checkAccess();

        $form = $this->formFactory->create(FormFactory::TYPE_HOME_TAB, array(), $homeTab);

        return array(
            'form' => $form->createView(),
            'tool' => $this->getHomeTool(),
            'homeTab' => $homeTab,
            'homeTabName' => $homeTab->getName()
        );
    }

    /**
     * @EXT\Route(
     *     "desktop/user/home_tab/{homeTabId}/{homeTabName}/edit",
     *     name="claro_user_desktop_home_tab_edit"
     * )
     * @EXT\Method("POST")
     * @EXT\ParamConverter(
     *     "homeTab",
     *     class="ClarolineCoreBundle:Home\HomeTab",
     *     options={"id" = "homeTabId", "strictId" = true}
     * )
     * @EXT\Template("ClarolineCoreBundle:Tool\desktop\home:userDesktopHomeTabEditForm.html.twig")
     *
     * Edit the homeTab.
     *
     * @return Response
     */
    public function userDesktopHomeTabEditAction(HomeTab $homeTab, $homeTabName)
    {
        $this->checkAccess();

        $form = $this->formFactory->create(FormFactory::TYPE_HOME_TAB, array(), $homeTab);
        $request = $this->getRequest();
        $form->handleRequest($request);

        if ($form->isValid()) {
            $this->homeTabManager->insertHomeTab($homeTab);

            return $this->redirect(
                $this->generateUrl('claro_desktop_home_tab_properties')
            );
        }

        return array(
            'form' => $form->createView(),
            'tool' => $this->getHomeTool(),
            'homeTab' => $homeTab,
            'homeTabName' => $homeTabName
        );
    }

    /**
     * @EXT\Route(
     *     "desktop/user/home_tab/{homeTabId}/delete",
     *     name="claro_user_desktop_home_tab_delete",
     *     options = {"expose"=true}
     * )
     * @EXT\Method("DELETE")
     * @EXT\ParamConverter(
     *     "homeTab",
     *     class="ClarolineCoreBundle:Home\HomeTab",
     *     options={"id" = "homeTabId", "strictId" = true}
     * )
     *
     * Delete the given homeTab.
     *
     * @return Response
     */
    public function userDesktopHomeTabDeleteAction(HomeTab $homeTab)
    {
        $this->checkAccess();

        $this->homeTabManager->deleteHomeTab($homeTab);

        return new Response('success', 204);
    }

    private function checkAccess()
    {
        if (!$this->securityContext->isGranted('ROLE_USER')) {
            throw new AccessDeniedException();
        }
    }

    private function getHomeTool()
    {
        return $this->toolManager->getOneToolByName('home');
    }
}
