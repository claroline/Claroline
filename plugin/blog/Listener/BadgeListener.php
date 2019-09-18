<?php

namespace Icap\BlogBundle\Listener;

use Icap\BlogBundle\Event\Log\LogCommentCreateEvent;
use Icap\BlogBundle\Event\Log\LogCommentDeleteEvent;
use Icap\BlogBundle\Event\Log\LogPostCreateEvent;
use Icap\BlogBundle\Event\Log\LogPostDeleteEvent;
use Icap\BlogBundle\Event\Log\LogPostReadEvent;
use Icap\BlogBundle\Event\Log\LogPostUpdateEvent;
use JMS\DiExtraBundle\Annotation as DI;
use Symfony\Bundle\FrameworkBundle\Routing\Router;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/**
 * @DI\Service("icap.listener.blog.badge_listener")
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
     * @DI\Observe("badge-resource-icap_blog-post_create-generate_validation_link")
     * @DI\Observe("badge-resource-icap_blog-post_delete-generate_validation_link")
     * @DI\Observe("badge-resource-icap_blog-post_read-generate_validation_link")
     * @DI\Observe("badge-resource-icap_blog-post_update-generate_validation_link")
     * @DI\Observe("badge-resource-icap_blog-comment_create-generate_validation_link")
     * @DI\Observe("badge-resource-icap_blog-comment_delete-generate_validation_link")
     */
    public function onBagdeCreateValidationLink($event)
    {
        $content = null;
        $log = $event->getLog();

        switch ($log->getAction()) {
            case LogPostCreateEvent::ACTION:
            case LogPostDeleteEvent::ACTION:
            case LogPostReadEvent::ACTION:
            case LogPostUpdateEvent::ACTION:
                $logDetails = $event->getLog()->getDetails();
                $node = $event->getLog()->getResourceNode();

                $url = $this->router->generate('claro_index', [], UrlGeneratorInterface::ABSOLUTE_PATH).
                    '#/desktop/workspaces/open/'.$node->getWorkspace()->getSlug().'/resources/'.$node->getSlug().
                    '/'.$logDetails['post']['title'];
                $title = $logDetails['post']['title'];
                $content = sprintf('<a href="%s" title="%s">%s</a>', $url, $title, $title);
                break;
            case LogCommentCreateEvent::ACTION:
            case LogCommentDeleteEvent::ACTION:
                $logDetails = $event->getLog()->getDetails();
                $node = $event->getLog()->getResourceNode();

                $url = $this->router->generate('claro_index', [], UrlGeneratorInterface::ABSOLUTE_PATH).
                    '#/desktop/workspaces/open/'.$node->getWorkspace()->getSlug().'/resources/'.$node->getSlug().
                    '/'.$logDetails['post']['title'];
                $title = $logDetails['post']['title'];
                $anchor = isset($logDetails['comment']['id']) ? '#comment-'.$logDetails['comment']['id'] : '';
                $content = sprintf('<a href="%s%s" title="%s">%s</a>', $url, $anchor, $title, $title);
                break;
        }

        $event->setContent($content);
        $event->stopPropagation();
    }
}
