<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace  Claroline\CoreBundle\Listener;

use JMS\DiExtraBundle\Annotation as DI;
use Claroline\CoreBundle\Entity\Widget\SimpleTextConfig;
use Claroline\CoreBundle\Form\Factory\FormFactory;
use Claroline\CoreBundle\Event\DisplayWidgetEvent;
use Claroline\CoreBundle\Event\CopyWidgetConfigurationEvent;
use Claroline\CoreBundle\Event\ConfigureWidgetEvent;
use Claroline\CoreBundle\Manager\SimpleTextManager;
use Claroline\CoreBundle\Persistence\ObjectManager;
use Symfony\Bundle\TwigBundle\TwigEngine;

/**
 * @DI\Service
 */
class SimpleTextWidgetListener
{
    private $simpleTextManager;
    private $formFactory;
    private $templating;
    private $om;
    private $router;

    /**
     * @DI\InjectParams({
     *      "simpleTextManager" = @DI\Inject("claroline.manager.simple_text_manager"),
     *      "formFactory"       = @DI\Inject("claroline.form.factory"),
     *      "templating"        = @DI\Inject("templating"),
     *      "om"                = @DI\Inject("claroline.persistence.object_manager"),
     *      "router"            = @DI\Inject("router")
     * })
     */
    public function __construct(
        SimpleTextManager $simpleTextManager,
        FormFactory $formFactory,
        TwigEngine $templating,
        ObjectManager $om,
        $router
    )
    {
        $this->simpleTextManager = $simpleTextManager;
        $this->formFactory = $formFactory;
        $this->templating = $templating;
        $this->om = $om;
        $this->router = $router;
    }

    /**
     * @DI\Observe("widget_simple_text")
     *
     * @param DisplayWidgetEvent $event
     */
    public function onDisplay(DisplayWidgetEvent $event)
    {
        $txtConfig = $this->simpleTextManager->getTextConfig($event->getInstance());
        if ($txtConfig) {
            $event->setContent($txtConfig->getContent());
        } else {
            $event->setContent('');
        }
        $event->stopPropagation();
    }

    /**
     * @DI\Observe("widget_simple_text_configuration")
     */
    public function onConfig(ConfigureWidgetEvent $event)
    {
        $instance = $event->getInstance();
        $txtConfig = $this->simpleTextManager->getTextConfig($instance);

        if ($txtConfig === null) {
            $txtConfig = new SimpleTextConfig();
            $txtConfig->setWidgetInstance($instance);
        }

        $form = $this->formFactory->create(
            FormFactory::TYPE_SIMPLE_TEXT,
            array('widget_text_'.rand(0, 1000000000)),
            $txtConfig
        );
        $content = $this->templating->render(
            'ClarolineCoreBundle:Widget:config_simple_text_form.html.twig',
            array(
                'form' => $form->createView(),
                'config' => $instance
            )
        );
        $event->setContent($content);
    }

    /**
     * @DI\Observe("copy_widget_config_simple_text")
     *
     * @param CopyWidgetConfigurationEvent $event
     */
    public function onCopyWidgetConfiguration(CopyWidgetConfigurationEvent $event)
    {
        $source = $event->getWidgetInstance();
        $copy = $event->getWidgetInstanceCopy();
        $widgetConfig = $this->simpleTextManager->getTextConfig($source);

        if (!is_null($widgetConfig)) {
            $widgetConfigCopy = new SimpleTextConfig();
            $widgetConfigCopy->setWidgetInstance($copy);
            $widgetConfigCopy->setContent($this->replaceLinks($widgetConfig->getContent(), $event->getResourceInfos()));

            $this->om->persist($widgetConfigCopy);
            $this->om->flush();
        }
        $event->validateCopy();
        $event->stopPropagation();
    }

    private function replaceLinks($content, $resourceInfos)
    {
        $baseUrl = $this->router->getContext()->getBaseUrl();

        foreach ($resourceInfos['copies'] as $resource) {

            $type = $resource['original']->getResourceType()->getName();
            
            $content = str_replace(
                '/file/resource/media/' . $resource['original']->getId(),
                '/file/resource/media/' . $resource['copy']->getId(),
                $content
            );

            $content = str_replace(
                "/resource/open/{$type}/" . $resource['original']->getId(),
                "/resource/open/{$type}/" . $resource['copy']->getId(),
                $content
            );
        }

        return $content;
    }
}
