<?php

namespace Claroline\PeerTubeBundle\Subscriber;

use Claroline\CoreBundle\Event\Layout\InjectJavascriptEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Twig\Environment;

class PluginSubscriber implements EventSubscriberInterface
{
    /** @var Environment */
    private $templating;

    public function __construct(
        Environment $templating
    ) {
        $this->templating = $templating;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            'layout.inject.javascript' => 'onInjectJs',
        ];
    }

    public function onInjectJs(InjectJavascriptEvent $event)
    {
        $event->addContent(
            $this->templating->render('@ClarolinePeerTube/scripts.html.twig')
        );
    }
}
