<?php

namespace Claroline\TagBundle\Listener\Platform;

use Claroline\CoreBundle\Event\Layout\InjectStylesheetEvent;
use JMS\DiExtraBundle\Annotation as DI;
use Symfony\Bridge\Twig\TwigEngine;

/**
 * @DI\Service
 */
class LayoutListener
{
    /**
     * @var TwigEngine
     */
    private $templating;

    /**
     * LayoutListener constructor.
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
    public function onInjectJs(InjectStylesheetEvent $event)
    {
        $content = $this->templating->render('ClarolineTagBundle:layout:stylesheets.html.twig', []);

        $event->addContent($content);
    }
}
