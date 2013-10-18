<?php

namespace  Claroline\CoreBundle\Listener\Log;

use Claroline\CoreBundle\Manager\LogManager;
use Symfony\Bundle\TwigBundle\TwigEngine;
use Symfony\Component\Form\FormFactory;
use Claroline\CoreBundle\Event\DisplayWidgetEvent;
use Claroline\CoreBundle\Form\Log\LogWorkspaceWidgetConfigType;
use Claroline\CoreBundle\Form\Log\LogDesktopWidgetConfigType;
use Claroline\CoreBundle\Manager\WorkspaceManager;
use Claroline\CoreBundle\Entity\Log\LogWidgetConfig;
use Claroline\CoreBundle\Event\ConfigureWidgetEvent;
use JMS\DiExtraBundle\Annotation as DI;

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
     * @DI\Observe("widget_core_resource_logger")
     *
     * @param DisplayWidgetEvent $event
     */
    public function onDisplay(DisplayWidgetEvent $event)
    {
        $instance = $event->getInstance();
        $params = ($instance->isDesktop()) ?
            $this->logManager->getDesktopWidgetList($instance):
            $this->logManager->getWorkspaceWidgetList($instance);

        $view = null;
        if ($params && count($params['logs']) > 0) {
            $view = $this->twig->render(
                'ClarolineCoreBundle:Log:view_short_list.html.twig',
                $params
            );
        }

        $event->setContent($view);
        $event->stopPropagation();
    }

    /**
     * @DI\Observe("widget_core_resource_logger_configuration")
     *
     * @param ConfigureWidgetEvent $event
     */
    public function onConfigure(ConfigureWidgetEvent $event)
    {
        $instance = $event->getInstance();
        $config = $this->logManager->getLogConfig($instance);

        if ($config === null) {
            $config = new LogWidgetConfig();
            $config->setWidgetInstance($instance);
        }

        if ($instance->isDesktop()) {
            $workspaces = array();
            $workspacesVisibility = array();
            if (!$instance->isAdmin()) {
               $workspaces = $this->workspaceManager->getWorkspacesByUserAndRoleNames(
                    $instance->getUser(),
                    array('ROLE_WS_COLLABORATOR', 'ROLE_WS_MANAGER')
                );
                $workspacesVisibility = $this
                    ->logManager
                    ->getWorkspaceVisibilityForDesktopWidget($instance->getUser(), $workspaces);
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
                    'instance' => $instance
                )
            );
        } else {
            $form    = $this->formFactory->create($this->logWorkspaceWidgetConfigForm, $config);
            $content = $this->twig->render(
                'ClarolineCoreBundle:Log:config_workspace_widget_form.html.twig',
                array(
                    'form'      => $form->createView(),
                    'instance' => $instance
                )
            );
        }

        $event->setContent($content);
    }
}
