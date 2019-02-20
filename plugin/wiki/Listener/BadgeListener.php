<?php

namespace Icap\WikiBundle\Listener;

use Icap\WikiBundle\Event\Log\LogContributionCreateEvent;
use Icap\WikiBundle\Event\Log\LogSectionCreateEvent;
use Icap\WikiBundle\Event\Log\LogSectionDeleteEvent;
use Icap\WikiBundle\Event\Log\LogSectionMoveEvent;
use Icap\WikiBundle\Event\Log\LogSectionRemoveEvent;
use Icap\WikiBundle\Event\Log\LogSectionRestoreEvent;
use Icap\WikiBundle\Event\Log\LogSectionUpdateEvent;
use JMS\DiExtraBundle\Annotation as DI;
use Symfony\Bundle\FrameworkBundle\Routing\Router;

/**
 * @DI\Service("icap.listener.wiki.badge_listener")
 */
class BadgeListener
{
    /** @var \Symfony\Bundle\FrameworkBundle\Routing\Router */
    private $router;

    /**
     * @DI\InjectParams({
     *     "router" = @DI\Inject("router")
     * })
     */
    public function __construct(Router $router)
    {
        $this->router = $router;
    }

    /**
     * @DI\Observe("badge-resource-icap_wiki-section_create-generate_validation_link")
     * @DI\Observe("badge-resource-icap_wiki-section_delete-generate_validation_link")
     * @DI\Observe("badge-resource-icap_wiki-section_move-generate_validation_link")
     * @DI\Observe("badge-resource-icap_wiki-section_remove-generate_validation_link")
     * @DI\Observe("badge-resource-icap_wiki-section_restore-generate_validation_link")
     * @DI\Observe("badge-resource-icap_wiki-section_update-generate_validation_link")
     * @DI\Observe("badge-resource-icap_wiki-contribution_create-generate_validation_link")
     */
    public function onBagdeCreateValidationLink($event)
    {
        $content = null;
        $log = $event->getLog();

        switch ($log->getAction()) {
            case LogSectionCreateEvent::ACTION:
            case LogSectionDeleteEvent::ACTION:
            case LogSectionMoveEvent::ACTION:
            case LogSectionRemoveEvent::ACTION:
            case LogSectionRestoreEvent::ACTION:
            case LogSectionUpdateEvent::ACTION:
            case LogContributionCreateEvent::ACTION:
        }

        $event->setContent($content);
        $event->stopPropagation();
    }
}
