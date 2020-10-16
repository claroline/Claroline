<?php

namespace Claroline\TagBundle\Listener\Platform;

use Claroline\CoreBundle\Event\Layout\InjectStylesheetEvent;
use Twig\Environment;

class ClientListener
{
    /** @var Environment */
    private $templating;

    /**
     * ClientListener constructor.
     */
    public function __construct(Environment $templating)
    {
        $this->templating = $templating;
    }

    public function onInjectCss(InjectStylesheetEvent $event)
    {
        $content = $this->templating->render('@ClarolineTag/layout/stylesheets.html.twig', []);

        $event->addContent($content);
    }
}
