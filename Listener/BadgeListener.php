<?php

namespace Icap\BlogBundle\Listener;

use Claroline\CoreBundle\Event\Badge\BadgeCreateValidationLinkEvent;
use Icap\BlogBundle\Event\Log\LogPostCreateEvent;
use Symfony\Bundle\FrameworkBundle\Routing\Router;
use Symfony\Bundle\FrameworkBundle\Translation\Translator;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use JMS\DiExtraBundle\Annotation as DI;

/**
 * @DI\Service("icap.listener.blog.badge_listener")
 */
class BadgeListener
{
    /** @var \Symfony\Bundle\FrameworkBundle\Routing\Router */
    private $router;

    /**
     * @DI\InjectParams({
     *     "router"     = @DI\Inject("router")
     * })
     */
    public function __construct(Router $router)
    {
        $this->router = $router;
    }

    /**
     * @DI\Observe("badge-resource-icap_blog-post_create-generate_validation_link")
     */
    public function onBagdeCreateValidationLink(BadgeCreateValidationLinkEvent $event)
    {
        $content = null;
        $log     = $event->getLog();

        switch($log->getAction())
        {
            case LogPostCreateEvent::ACTION:
                $logDetails = $event->getLog()->getDetails();
                $parameters = array(
                    'blogId'   => $logDetails['post']['blog'],
                    'postSlug' => $logDetails['post']['slug']
                );

                $url     = $this->router->generate('icap_blog_post_view', $parameters, UrlGeneratorInterface::ABSOLUTE_PATH);
                $title   = $logDetails['post']['title'];
                $content = sprintf('<a href="%s" title="%s">%s</a>', $url, $title, $title);
                break;
        }

        $event->setContent($content);
        $event->stopPropagation();
    }
}
