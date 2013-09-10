<?php

namespace  Claroline\CoreBundle\Listener\Log;

use Claroline\CoreBundle\Event\Log\LogGenericEvent;
use Claroline\CoreBundle\Manager\LogManager;
use Symfony\Bundle\TwigBundle\TwigEngine;
use Symfony\Component\Form\FormFactory;
use Symfony\Component\Security\Core\SecurityContextInterface;
use JMS\DiExtraBundle\Annotation as DI;
use Claroline\CoreBundle\Event\DisplayWidgetEvent;
use Claroline\CoreBundle\Event\ConfigureWidgetWorkspaceEvent;
use Claroline\CoreBundle\Event\ConfigureWidgetDesktopEvent;
use Claroline\CoreBundle\Entity\Log\LogWorkspaceWidgetConfig;
use Claroline\CoreBundle\Entity\Log\LogDesktopWidgetConfig;
use Claroline\CoreBundle\Form\Log\LogWorkspaceWidgetConfigType;
use Claroline\CoreBundle\Form\Log\LogDesktopWidgetConfigType;
use Claroline\CoreBundle\Manager\WorkspaceManager;

/**
 * @DI\Service
 */
class LogWidgetListener
{
    /** @var \Claroline\CoreBundle\Manager\LogManager */
    private $logManager;

    /** @var \Claroline\CoreBundle\Manager\WorkspaceManager */
    private $workspaceManager;

    /** @var \Symfony\Bundle\TwigBundle\TwigEngine */
    private $twig;

    /** @var \Claroline\CoreBundle\Form\Factory\FormFactory */
    private $formFactory;
    /**
     * @var \Claroline\CoreBundle\Form\Log\LogWorkspaceWidgetConfigType
     */
    private $logWorkspaceWidgetConfigForm;

    /**
     * @DI\InjectParams({
     *     "logManager"                   = @DI\Inject("claroline.log.manager"),
     *     "workspaceManager"             = @DI\Inject("claroline.manager.workspace_manager"),
     *     "twig"                         = @DI\Inject("templating"),
     *     "formFactory"                  = @DI\Inject("form.factory"),
     *     "logWorkspaceWidgetConfigForm" = @DI\Inject("claroline.form.logWorkspaceWidgetConfig")
     * })
     */
    public function __construct(
        LogManager $logManager,
        WorkspaceManager $workspaceManager,
        TwigEngine $twig,
        FormFactory $formFactory,
        LogWorkspaceWidgetConfigType $logWorkspaceWidgetConfigForm
    )
    {
        $this->logManager                   = $logManager;
        $this->workspaceManager             = $workspaceManager;
        $this->twig                         = $twig;
        $this->formFactory                  = $formFactory;
        $this->logWorkspaceWidgetConfigForm = $logWorkspaceWidgetConfigForm;
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
                    $config
                        ->setIsDefault(false)
                        ->setWorkspace($event->getWorkspace())
                        ->setRestrictions($this->logManager->getDefaultWorkspaceConfigRestrictions());
                }
            }
        }

        if ($config === null) {
            $config = new LogWorkspaceWidgetConfig();
            $config
                ->setIsDefault($event->isDefault())
                ->setWorkspace($event->getWorkspace())
                ->setRestrictions($this->logManager->getDefaultWorkspaceConfigRestrictions());
        }

        $form    = $this->formFactory->create($this->logWorkspaceWidgetConfigForm, $config);
        $content = $this->twig->render(
            'ClarolineCoreBundle:Log:config_workspace_widget_form.html.twig',
            array(
                'form'      => $form->createView(),
                'workspace' => $event->getWorkspace(),
                'isDefault' => $event->isDefault() ? 1 : 0
            )
        );
        $event->setContent($content);
    }

    /**
     * @DI\Observe("widget_core_resource_logger_configuration_desktop")
     *
     * @param \Claroline\CoreBundle\Event\ConfigureWidgetDesktopEvent $event
     */
    public function onDesktopConfigure(ConfigureWidgetDesktopEvent $event)
    {
        $workspaces = array();

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
