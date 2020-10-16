<?php

namespace Claroline\VideoPlayerBundle\Listener;

use Claroline\CoreBundle\Event\Layout\InjectJavascriptEvent;
use Twig\Environment;

class PluginListener
{
    /** @var Environment */
    private $templating;

    /**
     * VideoPlayerListener constructor.
     */
    public function __construct(
        Environment $templating
    ) {
        $this->templating = $templating;
    }

    public function onInjectJs(InjectJavascriptEvent $event)
    {
        $event->addContent(
            $this->templating->render('ClarolineVideoPlayerBundle::scripts.html.twig')
        );
    }
}
