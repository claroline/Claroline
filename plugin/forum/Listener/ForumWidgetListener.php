<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\ForumBundle\Listener;

use Claroline\CoreBundle\Event\ConfigureWidgetEvent;
use Claroline\CoreBundle\Event\DisplayWidgetEvent;
use Claroline\CoreBundle\Listener\NoHttpRequestException;
use Claroline\ForumBundle\Entity\Widget\LastMessageWidgetConfig;
use Claroline\ForumBundle\Form\Widget\LastMessageWidgetConfigType;
use Claroline\ForumBundle\Manager\Manager;
use JMS\DiExtraBundle\Annotation as DI;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Templating\EngineInterface;

/**
 * @DI\Service()
 */
class ForumWidgetListener
{
    private $formFactory;
    private $forumManager;
    private $request;
    private $templatingEngine;

    /**
     * @DI\InjectParams({
     *     "formFactory"       = @DI\Inject("form.factory"),
     *     "forumManager"      = @DI\Inject("claroline.manager.forum_manager"),
     *     "requestStack"      = @DI\Inject("request_stack"),
     *     "templatingEngine"  = @DI\Inject("templating")
     * })
     */
    public function __construct(
        FormFactoryInterface $formFactory,
        Manager $forumManager,
        RequestStack $requestStack,
        EngineInterface $templatingEngine
    ) {
        $this->formFactory = $formFactory;
        $this->forumManager = $forumManager;
        $this->request = $requestStack->getCurrentRequest();
        $this->templatingEngine = $templatingEngine;
    }

    /**
     * @DI\Observe("widget_claroline_forum_widget")
     *
     * @param DisplayWidgetEvent $event
     *
     * @throws \Claroline\CoreBundle\Listener\NoHttpRequestException
     */
    public function onDisplay(DisplayWidgetEvent $event)
    {
        if (!$this->request) {
            throw new NoHttpRequestException();
        }
        $widgetInstance = $event->getInstance();
        $workspace = $widgetInstance->getWorkspace();

        if ($workspace == null) {
            $templatePath = 'ClarolineForumBundle:Forum:forumsDesktopWidget.html.twig';
            $widgetType = 'desktop';
        } else {
            $templatePath = 'ClarolineForumBundle:Forum:forumsWorkspaceWidget.html.twig';
            $widgetType = 'workspace';
        }
        $messages = $this->forumManager->getLastMessages($widgetInstance);
        $event->setContent($this->templatingEngine->render(
            $templatePath,
            array(
                'widgetType' => $widgetType,
                'messages' => $messages,
            )
        ));
        $event->stopPropagation();
    }

    /**
     * @DI\Observe("widget_claroline_forum_widget_configuration")
     */
    public function onConfigure(ConfigureWidgetEvent $event)
    {
        $widgetInstance = $event->getInstance();
        $lastMessageWidgetConfig = $this->forumManager->getConfig($widgetInstance);

        if (is_null($lastMessageWidgetConfig)) {
            $lastMessageWidgetConfig = new LastMessageWidgetConfig();
            $lastMessageWidgetConfig->setWidgetInstance($widgetInstance);
        }
        $form = $this->formFactory->create(new LastMessageWidgetConfigType(), $lastMessageWidgetConfig);
        $content = $this->templatingEngine->render(
            'ClarolineForumBundle:Widget:lastMessageWidgetConfig.html.twig',
            array(
                'form' => $form->createView(),
                'widgetInstance' => $widgetInstance,
            )
        );
        $event->setContent($content);
    }
}
