<?php

namespace  Claroline\CoreBundle\Listener;

use JMS\DiExtraBundle\Annotation as DI;
use Claroline\CoreBundle\Entity\Widget\SimpleTextConfig;
use Claroline\CoreBundle\Form\Factory\FormFactory;
use Claroline\CoreBundle\Event\DisplayWidgetEvent;
use Claroline\CoreBundle\Event\ConfigureWidgetEvent;
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
     * @DI\Observe("widget_simple_text_configuration")
     */
    public function onConfig(ConfigureWidgetEvent $event)
    {
        $config = $event->getConfig();
        $txtConfig = $this->simpleTextManager->getTextConfig($config);
        
        if ($txtConfig === null) {
            $txtConfig = new SimpleTextConfig();
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
