<?php

namespace Icap\LessonBundle\Controller;

use Claroline\AppBundle\API\Crud;
use Claroline\AppBundle\API\Options;
use Claroline\AppBundle\API\SerializerProvider;
use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Library\Normalizer\TextNormalizer;
use Claroline\CoreBundle\Security\PermissionCheckerTrait;
use Icap\LessonBundle\Entity\Chapter;
use Icap\LessonBundle\Entity\Lesson;
use Icap\LessonBundle\Manager\ChapterManager;
use Icap\LessonBundle\Manager\PdfManager;
use Icap\LessonBundle\Repository\ChapterRepository;
use Icap\LessonBundle\Serializer\ChapterSerializer;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

#[Route(path: '/lesson/{lessonId}/chapters')]
class ChapterController
{
    use PermissionCheckerTrait;

    private ChapterRepository $chapterRepository;

    public function __construct(
        AuthorizationCheckerInterface $authorization,
        private readonly ObjectManager $om,
        private readonly Crud $crud,
        private readonly SerializerProvider $serializer,
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

    /**
     * Get chapter by its slug.
     */
    #[Route(path: '/{slug}', name: 'apiv2_lesson_chapter_get', methods: ['GET'])]
    public function getAction(#[MapEntity(mapping: ['lessonId' => 'uuid'])] Lesson $lesson, string $slug): JsonResponse
    {
        $this->checkPermission('OPEN', $lesson->getResourceNode(), [], true);

        $chapter = $this->chapterRepository->findOneBy([
            'slug' => $slug,
            'lesson' => $lesson,
        ]);

        if (is_null($chapter)) {
            throw new NotFoundHttpException();
        }

        $internalNotes = $this->checkPermission('VIEW_INTERNAL_NOTES', $lesson->getResourceNode());

        return new JsonResponse($this->serializer->serialize($chapter, $internalNotes ? [ChapterSerializer::INCLUDE_INTERNAL_NOTES] : []));
    }

    /**
     * Create a new chapter.
     */
    #[Route(path: '/{parentSlug}', name: 'apiv2_lesson_chapter_create', methods: ['POST'])]
    public function createAction(
        Request $request,
        #[MapEntity(mapping: ['lessonId' => 'uuid'])]
        Lesson $lesson,
        #[MapEntity(mapping: ['parentSlug' => 'slug'])]
        ?Chapter $parent = null
    ): JsonResponse {
        $this->checkPermission('EDIT', $lesson->getResourceNode(), [], true);

        $newChapter = $this->chapterManager->createChapter($lesson, json_decode($request->getContent(), true), $parent, [Crud::NO_PERMISSIONS, Options::PERSIST_TAG]);
        $internalNotes = $this->checkPermission('VIEW_INTERNAL_NOTES', $lesson->getResourceNode());

        return new JsonResponse(
            $this->serializer->serialize($newChapter, $internalNotes ? [ChapterSerializer::INCLUDE_INTERNAL_NOTES] : [])
        );
    }

    /**
     * Update an existing chapter.
     */
    #[Route(path: '/{slug}', name: 'apiv2_lesson_chapter_update', methods: ['PUT'])]
    public function updateAction(
        Request $request,
        #[MapEntity(mapping: ['lessonId' => 'uuid'])]
        Lesson $lesson,
        #[MapEntity(mapping: ['slug' => 'slug'])]
        Chapter $chapter
    ): JsonResponse {
        $this->checkPermission('EDIT', $lesson->getResourceNode(), [], true);

        $this->crud->update($chapter, json_decode($request->getContent(), true), [Options::PERSIST_TAG]);
        $internalNotes = $this->checkPermission('VIEW_INTERNAL_NOTES', $lesson->getResourceNode());

        return new JsonResponse($this->serializer->serialize($chapter, $internalNotes ? [ChapterSerializer::INCLUDE_INTERNAL_NOTES] : []));
    }

    /**
     * Delete existing chapter.
     */
    #[Route(path: '/{slug}', name: 'apiv2_lesson_chapter_delete', methods: ['DELETE'])]
    public function deleteAction(
        #[MapEntity(mapping: ['lessonId' => 'uuid'])]
        Lesson $lesson,
        #[MapEntity(mapping: ['slug' => 'slug'])]
        Chapter $chapter
    ): JsonResponse {
        $this->checkPermission('EDIT', $lesson->getResourceNode(), [], true);

        $this->crud->delete($chapter, [Crud::NO_PERMISSIONS]);

        $internalNotes = $this->authorization->isGranted('VIEW_INTERNAL_NOTES', $lesson->getResourceNode());
        $chapters = $this->om->getRepository(Chapter::class)->getChildren($lesson->getRoot(), false, null, 'ASC', true);

        // return the full chapters list for now to avoid having to recompute previousSlug/nextSlug in UI
        return new JsonResponse(array_map(function (Chapter $chapter) use ($internalNotes) {
            return $this->serializer->serialize($chapter, $internalNotes ? [ChapterSerializer::INCLUDE_INTERNAL_NOTES] : []);
        }, $chapters));
    }

    #[Route(path: '/{slug}/move', name: 'apiv2_lesson_chapter_move', methods: ['PUT'])]
    public function moveAction(
        #[MapEntity(mapping: ['lessonId' => 'uuid'])]
        Lesson $lesson,
        #[MapEntity(mapping: ['slug' => 'slug'])]
        Chapter $chapter,
        Request $request
    ): JsonResponse {
        $this->checkPermission('EDIT', $lesson->getResourceNode(), [], true);

        $this->chapterManager->moveChapter($chapter, json_decode($request->getContent(), true));

        $internalNotes = $this->authorization->isGranted('VIEW_INTERNAL_NOTES', $lesson->getResourceNode());
        $chapters = $this->om->getRepository(Chapter::class)->getChildren($lesson->getRoot(), false, null, 'ASC', true);

        // return the full chapters list for now to avoid having to recompute previousSlug/nextSlug in UI
        return new JsonResponse(array_map(function (Chapter $chapter) use ($internalNotes) {
            return $this->serializer->serialize($chapter, $internalNotes ? [ChapterSerializer::INCLUDE_INTERNAL_NOTES] : []);
        }, $chapters));
    }

    #[Route(path: '/{chapter}/pdf', name: 'icap_lesson_chapter_export_pdf', methods: ['GET'])]
    public function downloadPdfAction(
        #[MapEntity(mapping: ['chapter' => 'uuid'])]
        Chapter $chapter
    ): StreamedResponse {
        $lesson = $chapter->getLesson();

        if (!$lesson->getResourceNode()->isDownloadable() || !$this->checkPermission('OPEN', $lesson->getResourceNode())) {
            throw new AccessDeniedException('Chapter is not downloadable.');
        }

        $fileName = TextNormalizer::toKey($lesson->getResourceNode()->getName().'-'.$chapter->getTitle());

        return new StreamedResponse(function () use ($chapter): void {
            echo $this->pdfManager->renderChapter($chapter);
        }, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename='.$fileName.'.pdf',
        ]);
    }
}
