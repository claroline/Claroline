<?php

namespace Claroline\YouTubeBundle\Subscriber;

use Claroline\CoreBundle\Event\Layout\InjectJavascriptEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Twig\Environment;

class PluginSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private readonly Environment $templating
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            'layout.inject.javascript' => 'onInjectJs',
        ];
    }

    public function onInjectJs(InjectJavascriptEvent $event): void
    {
        $event->addContent(
            $this->templating->render('@ClarolineYouTube/scripts.html.twig')
        );
    }
}
