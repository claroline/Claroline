<?php

namespace Icap\LessonBundle\Controller\API;

use Claroline\CoreBundle\Security\PermissionCheckerTrait;
use Icap\LessonBundle\Entity\Chapter;
use Icap\LessonBundle\Entity\Lesson;
use Icap\LessonBundle\Manager\ChapterManager;
use Icap\LessonBundle\Repository\ChapterRepository;
use Icap\LessonBundle\Serializer\ChapterSerializer;
use JMS\DiExtraBundle\Annotation as DI;
use Sensio\Bundle\FrameworkExtraBundle\Configuration as EXT;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * @EXT\Route("lesson/{lessonId}", options={"expose"=true})
 * @EXT\ParamConverter("lesson", class="IcapLessonBundle:Lesson", options={"mapping": {"lessonId": "uuid"}})
 */
class ChapterController
{
    use PermissionCheckerTrait;

    /** @var ContainerInterface */
    private $container;

    /** @var ChapterManager */
    private $chapterManager;

    /** @var ChapterRepository */
    private $chapterRepository;

    /** @var ChapterSerializer */
    private $chapterSerializer;

    /**
     * chapterController constructor.
     *
     * @DI\InjectParams({
     *     "container" = @DI\Inject("service_container"),
     *     "chapterSerializer" = @DI\Inject("Icap\LessonBundle\Serializer\ChapterSerializer"),
     *     "chapterManager" = @DI\Inject("Icap\LessonBundle\Manager\ChapterManager")
     * })
     *
     * @param ContainerInterface $container
     * @param ChapterSerializer  $chapterSerializer
     * @param ChapterManager     $chapterManager
     */
    public function __construct(ContainerInterface $container, ChapterSerializer $chapterSerializer, ChapterManager $chapterManager)
    {
        $this->container = $container;
        $this->chapterRepository = $this->container->get('doctrine.orm.entity_manager')->getRepository('IcapLessonBundle:Chapter');
        $this->chapterSerializer = $chapterSerializer;
        $this->chapterManager = $chapterManager;
    }

    /**
     * Get the name of the managed entity.
     *
     * @return string
     */
    public function getName()
    {
        return 'chapter';
    }

    /**
     * Get chapter by its slug.
     *
     * @EXT\Route("/chapters/{chapterSlug}", name="apiv2_lesson_chapter_get")
     * @EXT\Method("GET")
     *
     * @param Request $request
     * @param Lesson  $lesson
     * @param string  $chapterSlug
     *
     * @return JsonResponse
     */
    public function getAction(Request $request, Lesson $lesson, $chapterSlug)
    {
        $this->checkPermission('OPEN', $lesson->getResourceNode(), [], true);

        $chapter = $this->chapterRepository->getChapterBySlug($chapterSlug, $lesson->getId());

        if (is_null($chapter)) {
            throw new NotFoundHttpException();
        }

        return new JsonResponse($this->chapterSerializer->serialize($chapter));
    }

    /**
     * Create new chapter.
     *
     * @EXT\Route("/chapters/{slug}", name="apiv2_lesson_chapter_create")
     * @EXT\Method("POST")
     * @EXT\ParamConverter("parent", class="IcapLessonBundle:Chapter", options={"mapping": {"slug": "slug"}})
     *
     * @param Request $request
     * @param Lesson  $lesson
     * @param Chapter $parent
     *
     * @return JsonResponse
     */
    public function createAction(Request $request, Lesson $lesson, Chapter $parent)
    {
        $this->checkPermission('EDIT', $lesson->getResourceNode(), [], true);

        $newChapter = $this->chapterManager->createChapter($lesson, json_decode($request->getContent(), true), $parent);

        return new JsonResponse($this->chapterSerializer->serialize($newChapter));
    }

    /**
     * Update existing chapter.
     *
     * @EXT\Route("/chapters/{slug}", name="apiv2_lesson_chapter_update")
     * @EXT\Method("PUT")
     * @EXT\ParamConverter("chapter", class="IcapLessonBundle:Chapter", options={"mapping": {"slug": "slug"}})
     *
     * @param Request $request
     * @param Lesson  $lesson
     * @param Chapter $chapter
     *
     * @return JsonResponse
     */
    public function editAction(Request $request, Lesson $lesson, Chapter $chapter)
    {
        $this->checkPermission('EDIT', $lesson->getResourceNode(), [], true);

        $this->chapterManager->updateChapter($lesson, $chapter, json_decode($request->getContent(), true));

        return new JsonResponse($this->chapterSerializer->serialize($chapter));
    }

    /**
     * Delete existing chapter.
     *
     * @EXT\Route("/chapters/{chapterSlug}/delete", name="apiv2_lesson_chapter_delete")
     * @EXT\Method("DELETE")
     * @EXT\ParamConverter("chapter", class="IcapLessonBundle:Chapter", options={"mapping": {"chapterSlug": "slug"}})
     *
     * @param Request $request
     * @param Lesson  $lesson
     * @param Chapter $chapter
     *
     * @return JsonResponse
     */
    public function deleteAction(Request $request, Lesson $lesson, Chapter $chapter)
    {
        $previousChapter = $this->chapterRepository->getPreviousChapter($chapter);
        $previousSlug = $previousChapter ? $previousChapter->getSlug() : null;

        $this->checkPermission('EDIT', $lesson->getResourceNode(), [], true);

        $payload = json_decode($request->getContent(), true);
        $deleteChildren = $payload['deleteChildren'];

        $this->chapterManager->deleteChapter($lesson, $chapter, $deleteChildren);

        return new JsonResponse([
            'tree' => $this->chapterManager->serializeChapterTree($lesson),
            'slug' => $previousSlug,
        ]);
    }

    /**
     * Get chapter tree.
     *
     * @EXT\Route("/tree", name="apiv2_lesson_tree_get")
     * @EXT\Method("GET")
     *
     * @param Lesson $lesson
     *
     * @return JsonResponse
     */
    public function getTreeAction(Lesson $lesson)
    {
        $this->checkPermission('OPEN', $lesson->getResourceNode(), [], true);

        return new JsonResponse($this->chapterManager->serializeChapterTree($lesson));
    }
}
