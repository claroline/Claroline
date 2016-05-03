<?php

namespace Icap\LessonBundle\Listener;

use Icap\LessonBundle\Event\Log\LogChapterCreateEvent;
use Icap\LessonBundle\Event\Log\LogChapterMoveEvent;
use Icap\LessonBundle\Event\Log\LogChapterReadEvent;
use Icap\LessonBundle\Event\Log\LogChapterUpdateEvent;
use Symfony\Bundle\FrameworkBundle\Routing\Router;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use JMS\DiExtraBundle\Annotation as DI;

/**
 * @DI\Service("icap.listener.lesson.badge_listener")
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
     * @DI\Observe("badge-resource-icap_lesson-chapter_create-generate_validation_link")
     * @DI\Observe("badge-resource-icap_lesson-chapter_delete-generate_validation_link")
     * @DI\Observe("badge-resource-icap_lesson-chapter_move-generate_validation_link")
     * @DI\Observe("badge-resource-icap_lesson-chapter_read-generate_validation_link")
     * @DI\Observe("badge-resource-icap_lesson-chapter_update-generate_validation_link")
     */
    public function onBagdeCreateValidationLink($event)
    {
        $content = null;
        $log = $event->getLog();

        switch ($log->getAction()) {
            case LogChapterCreateEvent::ACTION:
            case LogChapterMoveEvent::ACTION:
            case LogChapterReadEvent::ACTION:
            case LogChapterUpdateEvent::ACTION:
                $logDetails = $event->getLog()->getDetails();
                $parameters = array(
                    'resourceId' => $logDetails['chapter']['lesson'],
                    'chapterId' => $logDetails['chapter']['chapter'],
                );

                $url = $this->router->generate('icap_lesson_chapter', $parameters, UrlGeneratorInterface::ABSOLUTE_PATH);
                $title = $logDetails['chapter']['title'];
                $content = sprintf('<a href="%s" title="%s">%s</a>', $url, $title, $title);
                break;
        }

        $event->setContent($content);
        $event->stopPropagation();
    }
}
