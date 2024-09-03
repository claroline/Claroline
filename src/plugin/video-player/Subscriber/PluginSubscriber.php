<?php

namespace Claroline\VideoPlayerBundle\Subscriber;

use Claroline\AppBundle\Event\Client\InjectJavascriptEvent;
use Claroline\AppBundle\Event\ClientEvents;
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
            ClientEvents::JAVASCRIPTS => 'onInjectJs',
        ];
    }

    public function onInjectJs(InjectJavascriptEvent $event): void
    {
        $event->addContent(
            $this->templating->render('@ClarolineVideoPlayer/scripts.html.twig')
        );
    }
}
