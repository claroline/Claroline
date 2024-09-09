<?php

namespace Claroline\TagBundle\Subscriber;

use Claroline\AppBundle\Event\Client\ConfigureEvent;
use Claroline\AppBundle\Event\Client\InjectStylesheetEvent;
use Claroline\AppBundle\Event\ClientEvents;
use Claroline\TagBundle\Entity\Tag;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Twig\Environment;

class PlatformSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private readonly AuthorizationCheckerInterface $authorization,
        private readonly Environment $templating
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            ClientEvents::STYLESHEETS => 'onInjectCss',
            ClientEvents::CONFIGURE => 'onClientConfig',
        ];
    }

    public function onClientConfig(ConfigureEvent $event): void
    {
        $event->setParameters([
            'canCreateTags' => $this->authorization->isGranted('CREATE', new Tag()),
        ]);
    }

    public function onInjectCss(InjectStylesheetEvent $event): void
    {
        $content = $this->templating->render('@ClarolineTag/layout/stylesheets.html.twig', []);

        $event->addContent($content);
    }
}
