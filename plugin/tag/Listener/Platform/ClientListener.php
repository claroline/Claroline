<?php

namespace Claroline\TagBundle\Listener\Platform;

use Claroline\CoreBundle\Event\Layout\InjectStylesheetEvent;
use Symfony\Bridge\Twig\TwigEngine;

class ClientListener
{
    /** @var TwigEngine */
    private $templating;

    /**
     * ClientListener constructor.
     *
     * @param TwigEngine $templating
     */
    public function __construct(TwigEngine $templating)
    {
        $this->templating = $templating;
    }

    /**
     * @param InjectStylesheetEvent $event
     */
    public function onInjectCss(InjectStylesheetEvent $event)
    {
        $content = $this->templating->render('ClarolineTagBundle:layout:stylesheets.html.twig', []);

        $event->addContent($content);
    }
}
