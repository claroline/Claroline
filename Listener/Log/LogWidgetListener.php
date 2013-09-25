<?php

namespace  Claroline\CoreBundle\Listener\Log;

use Claroline\CoreBundle\Manager\LogManager;
use Symfony\Bundle\TwigBundle\TwigEngine;
use Symfony\Component\Form\FormFactory;
use Claroline\CoreBundle\Event\DisplayWidgetEvent;
use Claroline\CoreBundle\Form\Log\LogWorkspaceWidgetConfigType;
use Claroline\CoreBundle\Manager\WorkspaceManager;
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
    public function onDesktopDisplay(DisplayWidgetEvent $event)
    {
        $event->setContent('hi');
    }

    /**
     * @DI\Observe("widget_core_resource_logger_configuration")
     *
     * @param ConfigureWidgetEvent $event
     */
    public function onConfigure(ConfigureWidgetEvent $event)
    {
        $config = $event->getConfig();
        $txtConfig = $this->logManager->getLogConfig($config);
        
        if ($txtConfig === null) {
            $txtConfig = new LogDesktopWidgetConfig();
        }
        
        $form = $this->formFactory->create(FormFactory::TYPE_SIMPLE_TEXT, array(), $txtConfig);
        $content = $this->templating->render(
            'ClarolineCoreBundle:Widget:config_simple_text_form.html.twig',
            array(
                'form' => $form->createView(),
                'isAdmin' => $config->isAdmin(),
                'config' => $config
            )
        );
        $event->setContent($content);
    }
}
