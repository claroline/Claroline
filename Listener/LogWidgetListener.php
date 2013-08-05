<?php

namespace  Claroline\CoreBundle\Listener;

use Symfony\Bundle\TwigBundle\TwigEngine;
use Symfony\Component\Security\Core\SecurityContextInterface;
use JMS\DiExtraBundle\Annotation as DI;
use Claroline\CoreBundle\Event\Event\DisplayWidgetEvent;
use Claroline\CoreBundle\Event\Event\ConfigureWidgetWorkspaceEvent;
use Claroline\CoreBundle\Event\Event\ConfigureWidgetDesktopEvent;
use Claroline\CoreBundle\Entity\Logger\LogWorkspaceWidgetConfig;
use Claroline\CoreBundle\Entity\Logger\LogDesktopWidgetConfig;
use Claroline\CoreBundle\Form\LogWorkspaceWidgetConfigType;
use Claroline\CoreBundle\Form\LogDesktopWidgetConfigType;
use Claroline\CoreBundle\Manager\WorkspaceManager;

/**
 * @DI\Service
 */
class LogWidgetListener
{
    private $logManager;
    private $workspaceManager;
    private $securityContext;
    private $twig;
    private $ed;
    private $formFactory;

    private function convertConfigToFormData($config, $isDefault)
    {
        $data = array();

        $data['isDefault'] = $isDefault;

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
     *     "logManager"         = @DI\Inject("claroline.log.manager"),
     *     "workspaceManager"   = @DI\Inject("claroline.manager.workspace_manager"),
     *     "context"            = @DI\Inject("security.context"),
     *     "twig"               = @DI\Inject("templating"),
     *     "ed"                 = @DI\Inject("claroline.event.event_dispatcher"),
     *     "formFactory"        = @DI\Inject("form.factory")
     * })
     */
    public function __construct(
        $logManager,
        WorkspaceManager $workspaceManager,
        SecurityContextInterface $context,
        TwigEngine $twig,
        $ed,
        $formFactory
    )
    {
        $this->logManager = $logManager;
        $this->workspaceManager = $workspaceManager;
        $this->securityContext = $context;
        $this->twig = $twig;
        $this->ed = $ed;
        $this->formFactory = $formFactory;
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
        if ($event->isDefault() === true) {
            $config = $this->logManager->getDefaultWorkspaceWidgetConfig();
        } else {
            $config = $this->logManager->getWorkspaceWidgetConfig($event->getWorkspace());
            if ($config === null) {
                $defaultConfig = $this->logManager->getDefaultWorkspaceWidgetConfig();
                if ($defaultConfig !== null) {
                    $config = new LogWorkspaceWidgetConfig();
                    $config->copy($defaultConfig);
                    $config->setIsDefault(false);
                    $config->setWorkspace($event->getWorkspace());
                }
            }
        }

        if ($config === null) {
            var_dump('pas de config...');
            $config = new LogWorkspaceWidgetConfig();
            $config->setIsDefault($event->isDefault());
            $config->setWorkspace($event->getWorkspace());
        }

        $data = $this->convertConfigToFormData($config, $event->isDefault());

        $form = $this->formFactory->create(new LogWorkspaceWidgetConfigType(), $data);
        $content = $this->twig->render(
            'ClarolineCoreBundle:Log:config_workspace_widget_form.html.twig',
            array(
                'form' => $form->createView(),
                'workspace' => $event->getWorkspace(),
                'isDefault' => $event->isDefault() ? 1 : 0
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
        if ($event->isDefault() !== true) {
            $workspaces = $this->workspaceManager->getWorkspacesByUserAndRoleNames(
                $event->getUser(),
                array('ROLE_WS_COLLABORATOR', 'ROLE_WS_MANAGER')
            );
            $workspacesVisibility = $this
                ->logManager
                ->getWorkspaceVisibilityForDesktopWidget($event->getUser(), $workspaces);
        } else {
            $workspacesVisibility = array();
        }

        if ($event->isDefault() === true) {
            $config = $this->logManager->getDefaultDesktopWidgetConfig();
        } else {
            $config = $this->logManager->getDesktopWidgetConfig($event->getUser());
            if ($config === null) {
                $defaultConfig = $this->logManager->getDefaultDesktopWidgetConfig();
                if ($defaultConfig !== null) {
                    $config = new LogDesktopWidgetConfig();
                    $config->copy($defaultConfig);
                    $config->setIsDefault(false);
                    $config->setUser($event->getUser());
                }
            }
        }

        if ($config === null) {
            $config = new LogDesktopWidgetConfig();
            $config->setIsDefault($event->isDefault());
            $config->setUser($event->getUser());
        }

        $workspacesVisibility['amount'] = $config->getAmount();

        $form = $this
            ->formFactory
            ->create(
                new LogDesktopWidgetConfigType(),
                $workspacesVisibility,
                array('workspaces' => $workspaces)
            );
        $content = $this->twig->render(
            'ClarolineCoreBundle:Log:config_desktop_widget_form.html.twig',
            array(
                'form' => $form->createView(),
                'isDefault' => $event->isDefault() ? 1 : 0
            )
        );
        $event->setContent($content);
    }
}
