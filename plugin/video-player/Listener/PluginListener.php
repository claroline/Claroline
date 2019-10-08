<?php

namespace Claroline\VideoPlayerBundle\Listener;

use Claroline\CoreBundle\Event\Layout\InjectJavascriptEvent;
use Symfony\Bridge\Twig\TwigEngine;

class PluginListener
{
    /** @var TwigEngine */
    private $templating;

    /**
     * VideoPlayerListener constructor.
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
