<?php

namespace Claroline\CoreBundle\Listener\Widget;

use Claroline\CoreBundle\Event\DisplayWidgetEvent;
use Symfony\Bundle\TwigBundle\TwigEngine;
use Symfony\Component\Security\Core\SecurityContextInterface;
use JMS\DiExtraBundle\Annotation as DI;

/**
 * @DI\Service()
 */
class BadgeUsageWidgetListener
{
    private $templating;

    /**
     * @DI\InjectParams({
     *     "templating" = @DI\Inject("templating")
     * })
     */
    public function __construct(TwigEngine $templating)
    {
        $this->templating = $templating;
    }

    /**
     * @DI\Observe("widget_badge_usage")
     *
     * @param DisplayWidgetEvent $event
     */
    public function onDisplay(DisplayWidgetEvent $event)
    {
        $content = $this->templating->render(
            'ClarolineCoreBundle:Widget:badgeUsage.html.twig',
            array()
        );
        $event->setContent($content);
        $event->stopPropagation();
    }
}
