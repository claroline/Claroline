<?php

namespace Claroline\TagBundle\Listener\Platform;

use Claroline\CoreBundle\Event\Layout\InjectStylesheetEvent;
use JMS\DiExtraBundle\Annotation as DI;
use Symfony\Bridge\Twig\TwigEngine;

/**
 * @DI\Service
 */
class ClientListener
{
    /** @var TwigEngine */
    private $templating;

    /**
     * ClientListener constructor.
     *
     * @DI\InjectParams({
     *     "templating" = @DI\Inject("templating")
     * })
     *
     * @param TwigEngine $templating
     */
    public function __construct(TwigEngine $templating)
    {
        $this->templating = $templating;
    }

    /**
     * @DI\Observe("layout.inject.stylesheet")
     *
     * @param InjectStylesheetEvent $event
     */
    public function onInjectCss(InjectStylesheetEvent $event)
    {
        $content = $this->templating->render('ClarolineTagBundle:layout:stylesheets.html.twig', []);

        $event->addContent($content);
    }
}
