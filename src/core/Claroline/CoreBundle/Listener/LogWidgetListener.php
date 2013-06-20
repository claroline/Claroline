<?php

namespace  Claroline\CoreBundle\Listener;

use Symfony\Bundle\TwigBundle\TwigEngine;
use Symfony\Component\Security\Core\SecurityContextInterface;
use JMS\DiExtraBundle\Annotation as DI;
use Claroline\CoreBundle\Library\Event\DisplayWidgetEvent;
use Claroline\CoreBundle\Library\Event\ConfigureWidgetWorkspaceEvent;
use Claroline\CoreBundle\Library\Event\ConfigureWidgetDesktopEvent;
use Claroline\CoreBundle\Library\Event\LogCreateDelegateViewEvent;
use Claroline\CoreBundle\Library\Event\LogResourceChildUpdateEvent;
use Claroline\CoreBundle\Entity\Workspace\AbstractWorkspace;
use Claroline\CoreBundle\Library\Security\Utilities;
use Claroline\CoreBundle\Form\LogWorkspaceWidgetConfigType;
use Claroline\CoreBundle\Form\LogDesktopWidgetConfigType;

/**
 * @DI\Service
 */
class LogWidgetListener
{
    private $logManager;
    private $securityContext;
    private $twig;
    private $ed;
    private $formFactory;
    private $manager;

    private function convertConfigToFormData($config)
    {
        $data = array();

        $data['creation'] =
            $config->getResourceCopy() === true &&
            $config->getResourceCreate() === true &&
            $config->getResourceShortcut() === true;

        $data['read'] =
            $config->getResourceRead() === true &&
            $config->getWsToolRead() === true;

        $data['export'] = $config->getResourceExport() === true;

        $data['update'] =
            $config->getResourceUpdate() === true &&
            $config->getResourceUpdateRename() === true;

        $data['updateChild'] = $config->getResourceChildUpdate() === true;

        $data['delete'] = $config->getResourceDelete() === true;

        $data['move'] = $config->getResourceMove() === true;

        $data['subscribe'] =
            $config->getWsRoleSubscribeUser() === true &&
            $config->getWsRoleSubscribeGroup() === true &&
            $config->getWsRoleUnsubscribeUser() === true &&
            $config->getWsRoleUnsubscribeGroup() === true &&
            $config->getWsRoleChangeRight() === true &&
            $config->getWsRoleCreate() === true &&
            $config->getWsRoleDelete() === true &&
            $config->getWsRoleUpdate() === true;

        $data['amount'] = $config->getAmount();
        return $data;
    }

    /**
     * @DI\InjectParams({
     *     "logManager"  = @DI\Inject("claroline.log.manager"),
     *     "context"     = @DI\Inject("security.context"),
     *     "twig"        = @DI\Inject("templating"),
     *     "ed"          = @DI\Inject("event_dispatcher"),
     *     "formFactory" = @DI\Inject("form.factory"),
     *     "manager" = @DI\Inject("doctrine.orm.entity_manager")
     * })
     *
     * @param EntityManager             $em
     * @param SecurityContextInterface  $context
     * @param TwigEngine                $twig
     */
    public function __construct($logManager, SecurityContextInterface $context, TwigEngine $twig, $ed, $formFactory, $manager)
    {
        $this->logManager = $logManager;
        $this->securityContext = $context;
        $this->twig = $twig;
        $this->ed = $ed;
        $this->formFactory = $formFactory;
        $this->manager = $manager;
    }

    /**
     * @DI\Observe("widget_core_resource_logger_workspace")
     *
     * @param DisplayWidgetEvent $event
     */
    public function onWorkspaceDisplay(DisplayWidgetEvent $event)
    {
        $params = $this->logManager->getWorkspaceWidgetList($event->getWorkspace());

        $view = null;
        if ($params) {
            $view = $this->twig->render(
                'ClarolineCoreBundle:Log:view_short_list.html.twig',
                $params
            );
        }

        $event->setContent($view);
        $event->setTitle($params['title']);
        $event->stopPropagation();
    }

    /**
     * @DI\Observe("widget_core_resource_logger_desktop")
     *
     * @param DisplayWidgetEvent $event
     */
    public function onDesktopDisplay(DisplayWidgetEvent $event)
    {
        $params = $this->logManager->getDesktopWidgetList();

        $view = null;
        if ($params && count($params['logs']) > 0) {
            $view = $this->twig->render(
                'ClarolineCoreBundle:Log:view_short_list.html.twig',
                $params
            );
        }

        $event->setContent($view);
        $event->setTitle($params['title']);
        $event->stopPropagation();
    }

    /**
     * @DI\Observe("widget_core_resource_logger_configuration_workspace")
     *
     * @param ConfigureWidgetWorkspaceEvent $event
     */
    public function onWorkspaceConfigure(ConfigureWidgetWorkspaceEvent $event)
    {
        $config = $this->logManager->getWorkspaceWidgetConfig($event->getWorkspace());
        $data = $this->convertConfigToFormData($config);

        $form = $this->formFactory->create(new LogWorkspaceWidgetConfigType(), $data);
        $content = $this->twig->render(
                'ClarolineCoreBundle:Log:config_workspace_widget_form.html.twig', array(
                'form' => $form->createView(),
                'workspace' => $event->getWorkspace()
            )
        );
        $event->setContent($content);
    }

    /**
     * @DI\Observe("widget_core_resource_logger_configuration_desktop")
     *
     * @param ConfigureWidgetWorkspaceEvent $event
     */
    public function onDesktopConfigure(ConfigureWidgetDesktopEvent $event)
    {
        $workspaces = $this
            ->manager
            ->getRepository('ClarolineCoreBundle:Workspace\AbstractWorkspace')
            ->findByUserAndRoleNames($event->getUser(), array('ROLE_WS_COLLABORATOR', 'ROLE_WS_MANAGER'));

        $workspacesVisibility = $this
            ->logManager
            ->getWorkspaceVisibilityForDesktopWidget($event->getUser(), $workspaces);

        $config = $this->logManager->getDesktopWidgetConfig($event->getUser());
            
        $workspacesVisibility['amount'] = $config->getAmount();
        
        $form = $this
            ->formFactory
            ->create(
                new LogDesktopWidgetConfigType(),
                $workspacesVisibility,
                array('workspaces' => $workspaces)
            );
        $content = $this->twig->render(
                'ClarolineCoreBundle:Log:config_desktop_widget_form.html.twig', array(
                'form' => $form->createView(),
            )
        );
        $event->setContent($content);
    }
}