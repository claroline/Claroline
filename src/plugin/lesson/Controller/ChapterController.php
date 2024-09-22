<?php

namespace Icap\LessonBundle\Controller;

use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Claroline\AppBundle\API\FinderProvider;
use Claroline\AppBundle\Manager\PdfManager;
use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Library\Normalizer\TextNormalizer;
use Claroline\CoreBundle\Security\PermissionCheckerTrait;
use Icap\LessonBundle\Entity\Chapter;
use Icap\LessonBundle\Entity\Lesson;
use Icap\LessonBundle\Manager\ChapterManager;
use Icap\LessonBundle\Repository\ChapterRepository;
use Icap\LessonBundle\Serializer\ChapterSerializer;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Twig\Environment;

/**
 *
 * @todo refactor using AbstractCrudController
 */
#[Route(path: '/lesson/{lessonId}/chapters')]
class ChapterController
{
    use PermissionCheckerTrait;

    private ChapterRepository $chapterRepository;

    public function __construct(
        AuthorizationCheckerInterface $authorization,
        private readonly ObjectManager $om,
        private readonly Environment $templating,
        private readonly FinderProvider $finder,
        private readonly ChapterSerializer $chapterSerializer,
        private readonly ChapterManager $chapterManager,
        private readonly PdfManager $pdfManager
    ) {
        $this->authorization = $authorization;
        $this->chapterRepository = $this->om->getRepository(Chapter::class);
    }

    /**
     * Get the name of the managed entity.
     */
    public static function getName(): string
    {
        return 'chapter';
    }

    #[Route(path: '/', name: 'apiv2_lesson_chapter_list', methods: ['GET'])]
    public function searchAction(#[MapEntity(mapping: ['lessonId' => 'uuid'])] Lesson $lesson, Request $request): JsonResponse
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
     */
    #[Route(path: '/{slug}', name: 'apiv2_lesson_chapter_get', methods: ['GET'])]
    public function getAction(#[MapEntity(mapping: ['lessonId' => 'uuid'])] Lesson $lesson, string $slug): JsonResponse
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
     */
    #[Route(path: '/{slug}', name: 'apiv2_lesson_chapter_create', methods: ['POST'])]
    public function createAction(Request $request, #[MapEntity(mapping: ['lessonId' => 'uuid'])] Lesson $lesson, #[MapEntity(mapping: ['slug' => 'slug'])]
    Chapter $parent): JsonResponse
    {
        $this->checkPermission('EDIT', $lesson->getResourceNode(), [], true);

        $newChapter = $this->chapterManager->createChapter($lesson, json_decode($request->getContent(), true), $parent);
        $internalNotes = $this->checkPermission('VIEW_INTERNAL_NOTES', $lesson->getResourceNode());

        return new JsonResponse($this->chapterSerializer->serialize($newChapter, $internalNotes ? [ChapterSerializer::INCLUDE_INTERNAL_NOTES] : []));
    }

    /**
     * Update existing chapter.
     *
     */
    #[Route(path: '/{slug}', name: 'apiv2_lesson_chapter_update', methods: ['PUT'])]
    public function editAction(Request $request, #[MapEntity(mapping: ['lessonId' => 'uuid'])] Lesson $lesson, #[MapEntity(mapping: ['slug' => 'slug'])]
    Chapter $chapter): JsonResponse
    {
        $this->checkPermission('EDIT', $lesson->getResourceNode(), [], true);

        $this->chapterManager->updateChapter($lesson, $chapter, json_decode($request->getContent(), true));
        $internalNotes = $this->checkPermission('VIEW_INTERNAL_NOTES', $lesson->getResourceNode());

        return new JsonResponse($this->chapterSerializer->serialize($chapter, $internalNotes ? [ChapterSerializer::INCLUDE_INTERNAL_NOTES] : []));
    }

    /**
     * Delete existing chapter.
     *
     */
    #[Route(path: '/{slug}', name: 'apiv2_lesson_chapter_delete', methods: ['DELETE'])]
    public function deleteAction(Request $request, #[MapEntity(mapping: ['lessonId' => 'uuid'])] Lesson $lesson, #[MapEntity(mapping: ['slug' => 'slug'])]
    Chapter $chapter): JsonResponse
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

    #[Route(path: '/{chapter}/pdf', name: 'icap_lesson_chapter_export_pdf')]
    public function downloadPdfAction(
        #[MapEntity(mapping: ['chapter' => 'uuid'])]
        Chapter $chapter
    ): StreamedResponse {
        $lesson = $chapter->getLesson();

        $this->checkPermission('EXPORT', $lesson->getResourceNode(), [], true);

        $fileName = TextNormalizer::toKey($lesson->getResourceNode()->getName().'-'.$chapter->getTitle());

        return new StreamedResponse(function () use ($lesson, $chapter): void {
            echo $this->pdfManager->fromHtml(
                $this->templating->render('@IcapLesson/lesson/open.pdf.twig', [
                    '_resource' => $lesson,
                    'tree' => $this->chapterRepository->getChapterTree($chapter),
                ])
            );
        }, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename='.$fileName.'.pdf',
        ]);
    }
}
