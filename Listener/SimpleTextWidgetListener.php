<?php

namespace  Claroline\CoreBundle\Listener;

use JMS\DiExtraBundle\Annotation as DI;
use Claroline\CoreBundle\Entity\Widget\SimpleTextWorkspaceConfig;
use Claroline\CoreBundle\Form\Factory\FormFactory;
use Claroline\CoreBundle\Entity\Widget\SimpleTextDesktopConfig;
use Claroline\CoreBundle\Event\Event\DisplayWidgetEvent;
use Claroline\CoreBundle\Event\Event\ConfigureWidgetWorkspaceEvent;
use Claroline\CoreBundle\Event\Event\ConfigureWidgetDesktopEvent;
use Claroline\CoreBundle\Manager\SimpleTextManager;
use Symfony\Bundle\TwigBundle\TwigEngine;
use Symfony\Component\Security\Core\SecurityContextInterface;

/**
 * @DI\Service
 */
class SimpleTextWidgetListener
{
    private $simpleTextManager;
    private $formFactory;
    private $templating;
    private $sc;

    /**
     * @DI\InjectParams({
     *      "simpleTextManager" = @DI\Inject("claroline.manager.simple_text_manager"),
     *      "formFactory"       = @DI\Inject("claroline.form.factory"),
     *      "templating"        = @DI\Inject("templating"),
     *      "sc"                = @DI\Inject("security.context")
     * })
     */
    public function __construct(
        SimpleTextManager $simpleTextManager,
        FormFactory $formFactory,
        TwigEngine $templating,
        SecurityContextInterface $sc
    )
    {
        $this->simpleTextManager = $simpleTextManager;
        $this->formFactory = $formFactory;
        $this->templating = $templating;
        $this->sc = $sc;
    }

    /**
     * @DI\Observe("widget_simple_text_desktop")
     *
     * @param DisplayWidgetEvent $event
     */
    public function onDesktopDisplay(DisplayWidgetEvent $event)
    {
        $config = $this->simpleTextManager->getDisplayedConfigForDekstop($this->sc->getToken()->getUser());
        //check if the config is correct
        if ($config === null) {
            $event->setContent('');
            $event->stopPropagation();

            return;
        }

        $event->setContent($config->getContent());
        $event->stopPropagation();
    }

    /**
     * @DI\Observe("widget_simple_text_workspace")
     *
     * @param DisplayWidgetEvent $event
     */
    public function onWorkspaceDisplay(DisplayWidgetEvent $event)
    {
        $config = $this->simpleTextManager->getDisplayedConfigForWorkspace($event->getWorkspace());
        //check if the config is correct
        if ($config === null) {
            $event->setContent('');
            $event->stopPropagation();

            return;
        }

        $event->setContent($config->getContent());
        $event->stopPropagation();
    }

    /**
     * @DI\Observe("widget_simple_text_configuration_desktop")
     */
    public function onDesktopConfig(ConfigureWidgetDesktopEvent $event)
    {
        if ($event->isDefault() === true) {
            $config = $this->simpleTextManager->getDefaultDesktopWidgetConfig();
        } else {
            $config = $this->simpleTextManager->getDesktopWidgetConfig($event->getUser());
            if ($config === null) {
                $defaultConfig = $this->simpleTextManager->getDefaultDesktopWidgetConfig();
                if ($defaultConfig !== null) {
                    $config = new SimpleTextDesktopConfig();
                    $config->setContent($defaultConfig->getContent());
                    $config->setIsDefault(false);
                    $config->setUser($event->getUser());
                }
            }
        }

        if ($config === null) {
            $config = new SimpleTextDesktopConfig();
            $config->setIsDefault($event->isDefault());
            $config->setUser($event->getUser());
        }

        $form = $this->formFactory->create(FormFactory::TYPE_SIMPLE_TEXT, array(), $config);

        $content = $this->templating->render(
            'ClarolineCoreBundle:Widget:config_desktop_widget_simple_text_form.html.twig',
            array(
                'form' => $form->createView(),
                'isDefault' => $event->isDefault() ? 1 : 0
            )
        );
        $event->setContent($content);
    }

    /**
     * @DI\Observe("widget_simple_text_configuration_workspace")
     */
    public function onWorkspaceConfig(ConfigureWidgetWorkspaceEvent $event)
    {
        if ($event->isDefault() === true) {
            $config = $this->simpleTextManager->getDefaultWorkspaceWidgetConfig();
        } else {
            $config = $this->simpleTextManager->getWorkspaceWidgetConfig($event->getWorkspace());
            if ($config === null) {
                $defaultConfig = $this->simpleTextManager->getDefaultWorkspaceWidgetConfig();
                if ($defaultConfig !== null) {
                    $config = new SimpleTextWorkspaceConfig();
                    $config->setContent($config->getContent());
                    $config->setIsDefault(false);
                    $config->setWorkspace($event->getWorkspace());
                }
            }
        }

        if ($config === null) {
            $config = new SimpleTextWorkspaceConfig();
            $config->setIsDefault($event->isDefault());
            $config->setWorkspace($event->getWorkspace());
        }

        $form = $this->formFactory->create(FormFactory::TYPE_SIMPLE_TEXT, array(), $config);
        $content = $this->templating->render(
            'ClarolineCoreBundle:Widget:config_workspace_widget_simple_text_form.html.twig',
            array(
                'form' => $form->createView(),
                'workspace' => $event->getWorkspace(),
                'isDefault' => $event->isDefault() ? 1 : 0
            )
        );
        $event->setContent($content);
    }
}
