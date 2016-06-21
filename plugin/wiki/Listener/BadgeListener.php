<?php

namespace Icap\WikiBundle\Listener;

use Icap\WikiBundle\Event\Log\LogContributionCreateEvent;
use Icap\WikiBundle\Event\Log\LogSectionCreateEvent;
use Icap\WikiBundle\Event\Log\LogSectionDeleteEvent;
use Icap\WikiBundle\Event\Log\LogSectionMoveEvent;
use Icap\WikiBundle\Event\Log\LogSectionRemoveEvent;
use Icap\WikiBundle\Event\Log\LogSectionRestoreEvent;
use Icap\WikiBundle\Event\Log\LogSectionUpdateEvent;
use Symfony\Bundle\FrameworkBundle\Routing\Router;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use JMS\DiExtraBundle\Annotation as DI;

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
                $logDetails = $event->getLog()->getDetails();
                $parameters = array('wikiId' => $logDetails['section']['wiki']);
                $sectionAnchor = sprintf('#section-%s', $logDetails['section']['id']);
                $url = $this->router->generate('icap_wiki_view', $parameters, UrlGeneratorInterface::ABSOLUTE_PATH);
                $title = $logDetails['section']['title'];
                $content = sprintf('<a href="%s%s" title="%s">%s</a>', $url, $sectionAnchor, $title, $title);
                break;
            case LogContributionCreateEvent::ACTION:
                $logDetails = $event->getLog()->getDetails();
                $parameters = array('wikiId' => $logDetails['contribution']['wiki']);
                $sectionAnchor = sprintf('#section-%s', $logDetails['contribution']['section']);
                $url = $this->router->generate('icap_wiki_view', $parameters, UrlGeneratorInterface::ABSOLUTE_PATH);
                $title = $logDetails['contribution']['title'];
                $content = sprintf('<a href="%s%s" title="%s">%s</a>', $url, $sectionAnchor, $title, $title);
                break;
        }

        $event->setContent($content);
        $event->stopPropagation();
    }
}
