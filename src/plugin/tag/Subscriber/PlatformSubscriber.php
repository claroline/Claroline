<?php

namespace Claroline\TagBundle\Subscriber;

use Claroline\CoreBundle\Event\GenericDataEvent;
use Claroline\CoreBundle\Event\Layout\InjectStylesheetEvent;
use Claroline\TagBundle\Entity\Tag;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Twig\Environment;

class PlatformSubscriber implements EventSubscriberInterface
{
    /** @var AuthorizationCheckerInterface */
    private $authorization;
    /** @var Environment */
    private $templating;

    public function __construct(
        AuthorizationCheckerInterface $authorization,
        Environment $templating
    ) {
        $this->authorization = $authorization;
        $this->templating = $templating;
    }

    public static function getSubscribedEvents()
    {
        return [
            'layout.inject.stylesheet' => 'onInjectCss',
            'claroline_populate_client_config' => 'onPopulateConfig',
        ];
    }

    public function onPopulateConfig(GenericDataEvent $event)
    {
        $event->setResponse([
            'canCreateTags' => $this->authorization->isGranted('CREATE', new Tag()),
        ]);
    }

    public function onInjectCss(InjectStylesheetEvent $event)
    {
        $content = $this->templating->render('@ClarolineTag/layout/stylesheets.html.twig', []);

        $event->addContent($content);
    }
}
