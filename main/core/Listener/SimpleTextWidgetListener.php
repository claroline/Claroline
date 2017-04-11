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

use Claroline\CoreBundle\Entity\Widget\SimpleTextConfig;
use Claroline\CoreBundle\Event\ConfigureWidgetEvent;
use Claroline\CoreBundle\Event\CopyWidgetConfigurationEvent;
use Claroline\CoreBundle\Event\DisplayWidgetEvent;
use Claroline\CoreBundle\Form\SimpleTextType;
use Claroline\CoreBundle\Manager\SimpleTextManager;
use Claroline\CoreBundle\Persistence\ObjectManager;
use JMS\DiExtraBundle\Annotation as DI;
use Symfony\Bundle\TwigBundle\TwigEngine;
use Symfony\Component\Form\FormFactory;

/**
 * @DI\Service
 */
class SimpleTextWidgetListener
{
    private $simpleTextManager;
    private $formFactory;
    private $templating;
    private $om;

    /**
     * SimpleTextWidgetListener constructor.
     *
     * @DI\InjectParams({
     *      "simpleTextManager" = @DI\Inject("claroline.manager.simple_text_manager"),
     *      "formFactory"       = @DI\Inject("form.factory"),
     *      "templating"        = @DI\Inject("templating"),
     *      "om"                = @DI\Inject("claroline.persistence.object_manager")
     * })
     *
     * @param SimpleTextManager $simpleTextManager
     * @param FormFactory       $formFactory
     * @param TwigEngine        $templating
     * @param ObjectManager     $om
     */
    public function __construct(
        SimpleTextManager $simpleTextManager,
        FormFactory $formFactory,
        TwigEngine $templating,
        ObjectManager $om
    ) {
        $this->simpleTextManager = $simpleTextManager;
        $this->formFactory = $formFactory;
        $this->templating = $templating;
        $this->om = $om;
    }

    /**
     * @DI\Observe("widget_simple_text")
     *
     * @param DisplayWidgetEvent $event
     */
    public function onDisplay(DisplayWidgetEvent $event)
    {
        $widgetText = $this->simpleTextManager->getTextConfig($event->getInstance());
        $content = $this->templating->render('ClarolineCoreBundle:Widget:SimpleText\display.html.twig', [
            'content' => $widgetText ? $widgetText->getContent() : '',
        ]);

        $event->setContent($content);
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

        $form = $this->formFactory->create(new SimpleTextType('widget_text_'.rand(0, 1000000000)), $txtConfig);
        $content = $this->templating->render(
            'ClarolineCoreBundle:Widget:SimpleText\configure.html.twig',
            ['form' => $form->createView(), 'config' => $instance]
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
            $content = $this->replaceResourceLinks($widgetConfig->getContent(), $event->getResourceInfos());
            $widgetConfigCopy->setContent($this->replaceTabsLinks($content, $event->getTabsInfos()));

            $this->om->persist($widgetConfigCopy);
            $this->om->flush();
        }
        $event->validateCopy();
        $event->stopPropagation();
    }

    private function replaceResourceLinks($content, $resourceInfos)
    {
        foreach ($resourceInfos['copies'] as $resource) {
            $type = $resource['original']->getResourceType()->getName();

            $content = str_replace(
                '/file/resource/media/'.$resource['original']->getId(),
                '/file/resource/media/'.$resource['copy']->getId(),
                $content
            );

            $content = str_replace(
                "/resource/open/{$type}/".$resource['original']->getId(),
                "/resource/open/{$type}/".$resource['copy']->getId(),
                $content
            );
        }

        return $content;
    }

    private function replaceTabsLinks($content, $tabsInfos)
    {
        foreach ($tabsInfos as $tabInfo) {
            $oldWsId = $tabInfo['original']->getWorkspace()->getId();
            $newWsId = $tabInfo['copy']->getWorkspace()->getId();
            $content = str_replace(
                '/workspaces/'.$oldWsId.'/open/tool/home/tab/'.$tabInfo['original']->getId(),
                '/workspaces/'.$newWsId.'/open/tool/home/tab/'.$tabInfo['copy']->getId(),
                $content
            );
        }

        return $content;
    }
}
