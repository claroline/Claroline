<?php

namespace Icap\LessonBundle\Controller;

use Claroline\AppBundle\API\Crud;
use Claroline\AppBundle\API\SerializerProvider;
use Claroline\AppBundle\Controller\RequestDecoderTrait;
use Claroline\AppBundle\Manager\PdfManager;
use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Library\Normalizer\TextNormalizer;
use Claroline\CoreBundle\Security\PermissionCheckerTrait;
use Icap\LessonBundle\Entity\Chapter;
use Icap\LessonBundle\Entity\Lesson;
use Icap\LessonBundle\Repository\ChapterRepository;
use Sensio\Bundle\FrameworkExtraBundle\Configuration as EXT;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Twig\Environment;

class LessonController
{
    use PermissionCheckerTrait;
    use RequestDecoderTrait;

    private ChapterRepository $chapterRepo;

    public function __construct(
        AuthorizationCheckerInterface $authorization,
        private readonly ObjectManager $om,
        private readonly Environment $templating,
        private readonly Crud $crud,
        private readonly SerializerProvider $serializer,
        private readonly PdfManager $pdfManager
    ) {
        $this->authorization = $authorization;
        $this->chapterRepo = $this->om->getRepository(Chapter::class);
    }

    /**
     * @EXT\ParamConverter("lesson", class="Icap\LessonBundle\Entity\Lesson", options={"mapping": {"id": "uuid"}})
     */
    #[Route(path: '/lesson/{id}', name: 'icap_lesson_update', methods: ['PUT'])]
    public function updateAction(Lesson $lesson, Request $request): JsonResponse
    {
        $this->checkPermission('EDIT', $lesson->getResourceNode(), [], true);

        $data = $this->decodeRequest($request);
        $object = $this->crud->update(Lesson::class, $data);

        return new JsonResponse(
            $this->serializer->serialize($object)
        );
    }

    /**
     * @EXT\ParamConverter("lesson", class="Icap\LessonBundle\Entity\Lesson", options={"mapping": {"id": "uuid"}})
     */
    #[Route(path: '/lesson/{id}/pdf', name: 'icap_lesson_export_pdf')]
    public function downloadPdfAction(Lesson $lesson): StreamedResponse
    {
        $this->checkPermission('EXPORT', $lesson->getResourceNode(), [], true);

        $fileName = TextNormalizer::toKey($lesson->getResourceNode()->getName());

        return new StreamedResponse(function () use ($lesson): void {
            echo $this->pdfManager->fromHtml(
                $this->templating->render('@IcapLesson/lesson/open.pdf.twig', [
                    '_resource' => $lesson,
                    'tree' => $this->chapterRepo->getChapterTree($lesson->getRoot(), false),
                ])
            );
        }, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename='.$fileName.'.pdf',
        ]);
    }
}
