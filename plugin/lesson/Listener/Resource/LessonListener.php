<?php

namespace Icap\LessonBundle\Listener\Resource;

use Claroline\AppBundle\API\SerializerProvider;
use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Event\Resource\CopyResourceEvent;
use Claroline\CoreBundle\Event\Resource\DeleteResourceEvent;
use Claroline\CoreBundle\Event\Resource\LoadResourceEvent;
use Claroline\CoreBundle\Event\Resource\OpenResourceEvent;
use Claroline\CoreBundle\Library\Configuration\PlatformConfigurationHandler;
use Icap\LessonBundle\Entity\Lesson;
use Icap\LessonBundle\Manager\ChapterManager;
use Icap\LessonBundle\Repository\ChapterRepository;
use JMS\DiExtraBundle\Annotation as DI;
use Symfony\Bundle\FrameworkBundle\Templating\EngineInterface;
use Symfony\Component\HttpFoundation\Response;

/**
 * @DI\Service()
 */
class LessonListener
{
    /** @var EngineInterface */
    private $templating;

    /* @var ObjectManager */
    private $om;

    /** @var PlatformConfigurationHandler */
    private $config;

    /** @var SerializerProvider */
    private $serializer;

    /** @var ChapterManager */
    private $chapterManager;

    /** @var ChapterRepository */
    private $chapterRepository;

    /**
     * LessonListener constructor.
     *
     * @DI\InjectParams({
     *     "templating"     = @DI\Inject("templating"),
     *     "om"             = @DI\Inject("claroline.persistence.object_manager"),
     *     "config"                 = @DI\Inject("claroline.config.platform_config_handler"),
     *     "serializer"     = @DI\Inject("claroline.api.serializer"),
     *     "chapterManager" = @DI\Inject("icap.lesson.manager.chapter")
     * })
     *
     * @param EngineInterface              $templating
     * @param ObjectManager                $om
     * @param PlatformConfigurationHandler $config
     * @param SerializerProvider           $serializer
     * @param ChapterManager               $chapterManager
     */
    public function __construct(
        EngineInterface $templating,
        ObjectManager $om,
        PlatformConfigurationHandler $config,
        SerializerProvider $serializer,
        ChapterManager $chapterManager
    ) {
        $this->templating = $templating;
        $this->om = $om;
        $this->config = $config;
        $this->serializer = $serializer;
        $this->chapterManager = $chapterManager;

        $this->chapterRepository = $this->om->getRepository('IcapLessonBundle:Chapter');
    }

    /**
     * Loads a lesson.
     *
     * @DI\Observe("resource.icap_lesson.load")
     *
     * @param LoadResourceEvent $event
     */
    public function onLoad(LoadResourceEvent $event)
    {
        /** @var Lesson $lesson */
        $lesson = $event->getResource();
        $firstChapter = $this->chapterRepository->getFirstChapter($lesson);

        $event->setData([
            'exportPdfEnabled' => $this->config->getParameter('is_pdf_export_active'),
            'lesson' => $this->serializer->serialize($lesson),
            'tree' => $this->chapterManager->serializeChapterTree($lesson),
            'chapter' => $firstChapter ? $this->serializer->serialize($firstChapter) : null,
        ]);

        $event->stopPropagation();
    }

    /**
     * @DI\Observe("open_icap_lesson")
     *
     * @param OpenResourceEvent $event
     */
    public function onOpen(OpenResourceEvent $event)
    {
        /** @var Lesson $lesson */
        $lesson = $event->getResource();

        $content = $this->templating->render(
            'IcapLessonBundle:lesson:open.html.twig', [
                '_resource' => $lesson,
                'chapter' => $this->chapterRepository->getFirstChapter($lesson),
                'tree' => $this->chapterManager->serializeChapterTree($lesson),
                'root' => $lesson->getRoot(),
            ]
        );

        $event->setResponse(new Response($content));
        $event->stopPropagation();
    }

    /**
     * @DI\Observe("copy_icap_lesson")
     *
     * @param CopyResourceEvent $event
     */
    public function onCopy(CopyResourceEvent $event)
    {
        /** @var Lesson $lesson */
        $lesson = $event->getResource();

        $newLesson = new Lesson();
        $newLesson->setName($lesson->getResourceNode()->getName());

        $this->om->persist($newLesson);
        $this->om->flush();

        $this->chapterManager->copyRoot($lesson->getRoot(), $newLesson->getRoot());

        $event->setCopy($newLesson);
        $event->stopPropagation();
    }

    /**
     * @DI\Observe("delete_icap_lesson")
     *
     * @param DeleteResourceEvent $event
     */
    public function onDelete(DeleteResourceEvent $event)
    {
        $event->stopPropagation();
    }
}
