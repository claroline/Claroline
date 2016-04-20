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
use Claroline\ForumBundle\Manager\ForumWidgetManager;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use JMS\DiExtraBundle\Annotation as DI;
use Symfony\Component\Templating\EngineInterface;

/**
 * @DI\Service()
 */
class ForumWidgetListener
{
    /**
     * @var ForumWidgetManager
     */
    protected $forumWidgetManager;

    /**
     * @var FormFactoryInterface
     */
    protected $formFactory;

    /**
     * @var EngineInterface
     */
    private $templatingEngine;

    /**
     * @var null|\Symfony\Component\HttpFoundation\Request
     */
    private $request;

    /**
     * @var HttpKernelInterface
     */
    private $httpKernel;

    /**
     * @DI\InjectParams({
     *     "requestStack"   = @DI\Inject("request_stack"),
     *     "httpKernel"     = @DI\Inject("http_kernel"),
     *     "forumWidgetManager" =  @DI\Inject("claroline.manager.forum_widget"),
     *     "formFactory" =  @DI\Inject("form.factory"),
     *     "templatingEngine" = @DI\Inject("templating")
     * })
     */
    public function __construct(RequestStack $requestStack, HttpKernelInterface $httpKernel,
        ForumWidgetManager $forumWidgetManager, FormFactoryInterface $formFactory, EngineInterface $templatingEngine)
    {
        $this->request = $requestStack->getCurrentRequest();
        $this->httpKernel = $httpKernel;
        $this->forumWidgetManager = $forumWidgetManager;
        $this->formFactory = $formFactory;
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

        $workspace = $event->getInstance()->getWorkspace();

        $templatePath = 'ClarolineForumBundle:Forum:forumsWorkspaceWidget.html.twig';
        $widgetType = 'workspace';
        if ($workspace == null) {
            $templatePath = 'ClarolineForumBundle:Forum:forumsDesktopWidget.html.twig';
            $widgetType = 'desktop';
        }

        $event->setContent($this->templatingEngine->render(
            $templatePath,
            array(
                'widgetType' => $widgetType,
                'messages' => $this->forumWidgetManager->getLastMessages($event->getInstance(), $workspace),
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
        $lastMessageWidgetConfig = $this->forumWidgetManager->getConfig($widgetInstance);

        if ($lastMessageWidgetConfig === null) {
            $lastMessageWidgetConfig = new LastMessageWidgetConfig();
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
