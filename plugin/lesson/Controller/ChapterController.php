<?php

namespace Icap\LessonBundle\Controller;

use Claroline\AppBundle\API\FinderProvider;
use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Library\Configuration\PlatformConfigurationHandler;
use Claroline\CoreBundle\Library\Normalizer\TextNormalizer;
use Claroline\CoreBundle\Security\PermissionCheckerTrait;
use Dompdf\Dompdf;
use Icap\LessonBundle\Entity\Chapter;
use Icap\LessonBundle\Entity\Lesson;
use Icap\LessonBundle\Manager\ChapterManager;
use Icap\LessonBundle\Repository\ChapterRepository;
use Icap\LessonBundle\Serializer\ChapterSerializer;
use Sensio\Bundle\FrameworkExtraBundle\Configuration as EXT;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Templating\EngineInterface;

/**
 * @Route("/lesson/{lessonId}/chapters")
 * @EXT\ParamConverter("lesson", class="IcapLessonBundle:Lesson", options={"mapping": {"lessonId": "uuid"}})
 *
 * @todo refactor using AbstractCrudController
 */
class ChapterController
{
    use PermissionCheckerTrait;

    /** @var ObjectManager */
    private $om;
    /** @var PlatformConfigurationHandler */
    private $config;
    /** @var EngineInterface */
    private $templating;
    /** @var FinderProvider */
    private $finder;
    /** @var ChapterManager */
    private $chapterManager;

    /** @var ChapterRepository */
    private $chapterRepository;

    /** @var ChapterSerializer */
    private $chapterSerializer;

    /** @var AuthorizationCheckerInterface */
    private $authorization;

    public function __construct(
        ObjectManager $om,
        PlatformConfigurationHandler $config,
        EngineInterface $templating,
        FinderProvider $finder,
        ChapterSerializer $chapterSerializer,
        ChapterManager $chapterManager,
        AuthorizationCheckerInterface $authorization
    ) {
        $this->om = $om;
        $this->config = $config;
        $this->templating = $templating;
        $this->finder = $finder;
        $this->chapterSerializer = $chapterSerializer;
        $this->chapterManager = $chapterManager;
        $this->authorization = $authorization;

        $this->chapterRepository = $this->om->getRepository('IcapLessonBundle:Chapter');
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
     * @Route("/", name="apiv2_lesson_chapter_list", methods={"GET"})
     */
    public function searchAction(Lesson $lesson, Request $request): JsonResponse
    {
        $this->checkPermission('OPEN', $lesson->getResourceNode(), [], true);

        $query = $request->query->all();
        $query['hiddenFilters'] = ['lesson' => $lesson->getUuid()];

        $internalNotes = $this->checkPermission('VIEW_INTERNAL_NOTES', $lesson->getResourceNode());

        return new JsonResponse(
            $this->finder->search(Chapter::class, $query, $internalNotes ? [ChapterSerializer::INCLUDE_INTERNAL_NOTES] : [])
        );
    }

    /**
     * Get chapter by its slug.
     *
     * @Route("/{slug}", name="apiv2_lesson_chapter_get", methods={"GET"})
     */
    public function getAction(Lesson $lesson, $slug): JsonResponse
    {
        $this->checkPermission('OPEN', $lesson->getResourceNode(), [], true);

        $chapter = $this->chapterRepository->getChapterBySlug($slug, $lesson->getId());

        if (is_null($chapter)) {
            throw new NotFoundHttpException();
        }

        $internalNotes = $this->checkPermission('VIEW_INTERNAL_NOTES', $lesson->getResourceNode());

        return new JsonResponse($this->chapterSerializer->serialize($chapter, $internalNotes ? [ChapterSerializer::INCLUDE_INTERNAL_NOTES] : []));
    }

    /**
     * Create new chapter.
     *
     * @Route("/{slug}", name="apiv2_lesson_chapter_create", methods={"POST"})
     * @EXT\ParamConverter("parent", class="IcapLessonBundle:Chapter", options={"mapping": {"slug": "slug"}})
     */
    public function createAction(Request $request, Lesson $lesson, Chapter $parent): JsonResponse
    {
        $this->checkPermission('EDIT', $lesson->getResourceNode(), [], true);

        $newChapter = $this->chapterManager->createChapter($lesson, json_decode($request->getContent(), true), $parent);
        $internalNotes = $this->checkPermission('VIEW_INTERNAL_NOTES', $lesson->getResourceNode());

        return new JsonResponse($this->chapterSerializer->serialize($newChapter, $internalNotes ? [ChapterSerializer::INCLUDE_INTERNAL_NOTES] : []));
    }

    /**
     * Update existing chapter.
     *
     * @Route("/{slug}", name="apiv2_lesson_chapter_update", methods={"PUT"})
     * @EXT\ParamConverter("chapter", class="IcapLessonBundle:Chapter", options={"mapping": {"slug": "slug"}})
     */
    public function editAction(Request $request, Lesson $lesson, Chapter $chapter): JsonResponse
    {
        $this->checkPermission('EDIT', $lesson->getResourceNode(), [], true);

        $this->chapterManager->updateChapter($lesson, $chapter, json_decode($request->getContent(), true));
        $internalNotes = $this->checkPermission('VIEW_INTERNAL_NOTES', $lesson->getResourceNode());

        return new JsonResponse($this->chapterSerializer->serialize($chapter, $internalNotes ? [ChapterSerializer::INCLUDE_INTERNAL_NOTES] : []));
    }

    /**
     * Delete existing chapter.
     *
     * @Route("/{slug}", name="apiv2_lesson_chapter_delete", methods={"DELETE"})
     * @EXT\ParamConverter("chapter", class="IcapLessonBundle:Chapter", options={"mapping": {"slug": "slug"}})
     */
    public function deleteAction(Request $request, Lesson $lesson, Chapter $chapter): JsonResponse
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
     * @Route("/tree", name="apiv2_lesson_tree_get", methods={"GET"})
     */
    public function getTreeAction(Lesson $lesson): JsonResponse
    {
        $this->checkPermission('OPEN', $lesson->getResourceNode(), [], true);

        return new JsonResponse($this->chapterManager->serializeChapterTree($lesson));
    }

    /**
     * @Route("/{chapter}/pdf", name="icap_lesson_chapter_export_pdf")
     * @EXT\ParamConverter("chapter", class="IcapLessonBundle:Chapter", options={"mapping": {"chapter": "uuid"}})
     */
    public function downloadPdfAction(Chapter $chapter): StreamedResponse
    {
        $lesson = $chapter->getLesson();

        $this->checkPermission('EXPORT', $lesson->getResourceNode(), [], true);

        $domPdf = new Dompdf();
        $domPdf->set_option('isHtml5ParserEnabled', true);
        $domPdf->set_option('isRemoteEnabled', true);
        $domPdf->set_option('tempDir', $this->config->getParameter('server.tmp_dir'));
        $domPdf->loadHtml($this->templating->render('IcapLessonBundle:lesson:open.pdf.twig', [
            '_resource' => $lesson,
            'tree' => $this->chapterRepository->getChapterTree($chapter),
        ]));

        // Render the HTML as PDF
        $domPdf->render();

        $fileName = TextNormalizer::toKey($lesson->getResourceNode()->getName().'-'.$chapter->getTitle());

        return new StreamedResponse(function () use ($domPdf, $fileName) {
            echo $domPdf->output();
        }, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename='.$fileName.'.pdf',
        ]);
    }
}
