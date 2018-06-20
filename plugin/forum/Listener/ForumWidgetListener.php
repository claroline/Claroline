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
    private $request;
    private $templatingEngine;

    /**
     * @DI\InjectParams({
     *     "formFactory"       = @DI\Inject("form.factory"),
     *     "requestStack"      = @DI\Inject("request_stack"),
     *     "templatingEngine"  = @DI\Inject("templating")
     * })
     */
    public function __construct(
        FormFactoryInterface $formFactory,
        RequestStack $requestStack,
        EngineInterface $templatingEngine
    ) {
        $this->formFactory = $formFactory;
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
    }

    /**
     * @DI\Observe("widget_claroline_forum_widget_configuration")
     */
    public function onConfigure(ConfigureWidgetEvent $event)
    {
    }
}
