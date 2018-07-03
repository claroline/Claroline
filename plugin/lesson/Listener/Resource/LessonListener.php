<?php

namespace Icap\LessonBundle\Listener\Resource;

use Claroline\CoreBundle\Event\CreateFormResourceEvent;
use Claroline\CoreBundle\Event\CreateResourceEvent;
use Claroline\CoreBundle\Event\Resource\CopyResourceEvent;
use Claroline\CoreBundle\Event\Resource\DeleteResourceEvent;
use Claroline\CoreBundle\Event\Resource\OpenResourceEvent;
use Icap\LessonBundle\Entity\Lesson;
use Icap\LessonBundle\Form\LessonType;
use Icap\LessonBundle\Manager\ChapterManager;
use Icap\LessonBundle\Repository\ChapterRepository;
use JMS\DiExtraBundle\Annotation as DI;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Response;

/**
 * @DI\Service()
 */
class LessonListener
{
    private $container;

    /* @var ObjectManager */
    private $om;

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
     *     "container"      = @DI\Inject("service_container"),
     *     "chapterManager" = @DI\Inject("icap.lesson.manager.chapter")
     * })
     *
     * @param ContainerInterface $container
     * @param ChapterManager     $chapterManager
     */
    public function __construct(
        ContainerInterface $container,
        ChapterManager $chapterManager
    ) {
        $this->container = $container;
        $this->om = $container->get('claroline.persistence.object_manager');
        $this->serializer = $container->get('claroline.api.serializer');
        $this->chapterRepository = $this->container->get('doctrine.orm.entity_manager')->getRepository('IcapLessonBundle:Chapter');
        $this->chapterManager = $chapterManager;
    }

    /**
     * @DI\Observe("create_form_icap_lesson")
     *
     * @param CreateFormResourceEvent $event
     */
    public function onCreateForm(CreateFormResourceEvent $event)
    {
        $form = $this->container->get('form.factory')->create(new LessonType(), new Lesson());
        $content = $this->container->get('templating')->render(
            'ClarolineCoreBundle:resource:create_form.html.twig',
            [
                'form' => $form->createView(),
                'resourceType' => 'icap_lesson',
            ]
        );

        $event->setResponseContent($content);
        $event->stopPropagation();
    }

    /**
     * @DI\Observe("create_icap_lesson")
     *
     * @param CreateResourceEvent $event
     */
    public function onCreate(CreateResourceEvent $event)
    {
        $request = $this->container->get('request_stack')->getMasterRequest();
        $form = $this->container->get('form.factory')->create(new LessonType(), new Lesson());
        $form->handleRequest($request);
        if ($form->isValid()) {
            $lesson = $form->getData();
            $event->setResources([$lesson]);
        } else {
            $content = $this->container->get('templating')->render(
                'ClarolineCoreBundle:Resource:create_form.html.twig',
                [
                    'form' => $form->createView(),
                    'resourceType' => 'icap_lesson',
                ]
            );
            $event->setErrorFormContent($content);
        }
        $event->stopPropagation();
    }

    /**
     * @DI\Observe("open_icap_lesson")
     *
     * @param OpenResourceEvent $event
     */
    public function onOpen(OpenResourceEvent $event)
    {
        /** @var Path $path */
        $lesson = $event->getResource();

        $content = $this->container->get('templating')->render(
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
        $entityManager = $this->container->get('doctrine.orm.entity_manager');
        $lesson = $event->getResource();

        $newLesson = new Lesson();
        $newLesson->setName($lesson->getResourceNode()->getName());
        $entityManager->persist($newLesson);
        $entityManager->flush($newLesson);

        //$chapterRepository = $entityManager->getRepository('IcapLessonBundle:Chapter');
        $chapter_manager = $this->container->get('icap.lesson.manager.chapter');
        $chapter_manager->copyRoot($lesson->getRoot(), $newLesson->getRoot());

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
        $om = $this->container->get('claroline.persistence.object_manager');
        $lesson = $event->getResource();
        $om->remove($lesson);
        $om->flush();
        $event->stopPropagation();
    }
}
