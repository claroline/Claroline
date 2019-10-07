<?php

namespace Claroline\VideoPlayerBundle\Listener;

use Claroline\CoreBundle\Event\Layout\InjectJavascriptEvent;
use JMS\DiExtraBundle\Annotation as DI;
use Symfony\Bridge\Twig\TwigEngine;

class PluginListener
{
    /** @var TwigEngine */
    private $templating;

    /**
     * VideoPlayerListener constructor.
     *
     * @DI\InjectParams({
     *     "templating"      = @DI\Inject("templating")
     * })
     *
     * @param TwigEngine $templating
     */
    public function __construct(
        TwigEngine $templating
    ) {
        $this->templating = $templating;
    }

    /**
     * @param InjectJavascriptEvent $event
     */
    public function onInjectJs(InjectJavascriptEvent $event)
    {
        $event->addContent(
            $this->templating->render('ClarolineVideoPlayerBundle::scripts.html.twig')
        );
    }
}
