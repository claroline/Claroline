<?php

namespace Icap\LessonBundle\Controller\Resource;

use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Security\PermissionCheckerTrait;
use Icap\LessonBundle\Entity\Chapter;
use Icap\LessonBundle\Entity\Lesson;
use Icap\LessonBundle\Repository\ChapterRepository;
use Sensio\Bundle\FrameworkExtraBundle\Configuration as EXT;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Bundle\TwigBundle\TwigEngine;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

class LessonController extends Controller
{
    use PermissionCheckerTrait;

    /** @var ObjectManager */
    private $om;
    /** @var TwigEngine */
    private $templating;

    /** @var ChapterRepository */
    private $chapterRepo;

    /**
     * LessonController constructor.
     *
     * @param AuthorizationCheckerInterface $authorization
     * @param ObjectManager                 $om
     * @param TwigEngine                    $templating
     */
    public function __construct(
        AuthorizationCheckerInterface $authorization,
        ObjectManager $om,
        TwigEngine $templating
    ) {
        $this->authorization = $authorization;
        $this->om = $om;
        $this->templating = $templating;

        $this->chapterRepo = $this->om->getRepository(Chapter::class);
    }

    /**
     * @EXT\Route("view/{lesson}.pdf", name="icap_lesson_export_pdf")
     * @EXT\ParamConverter("lesson", class="IcapLessonBundle:Lesson", options={"mapping": {"lesson": "uuid"}})
     *
     * @param Lesson $lesson
     *
     * @return JsonResponse
     */
    public function viewLessonPdfAction(Lesson $lesson)
    {
        $this->checkPermission('EXPORT', $lesson->getResourceNode(), [], true);

        return new JsonResponse([
            'name' => $lesson->getResourceNode()->getName(),
            'content' => $this->templating->render(
                'IcapLessonBundle:lesson:open.pdf.twig', [
                    '_resource' => $lesson,
                    'tree' => $this->chapterRepo->getChapterTree($lesson->getRoot(), false),
                ]
            ),
        ]);
    }

    /**
     * @EXT\Route("view/chapter/{chapter}.pdf", name="icap_lesson_chapter_export_pdf")
     * @EXT\ParamConverter("chapter", class="IcapLessonBundle:Chapter", options={"mapping": {"chapter": "uuid"}})
     *
     * @param Chapter $chapter
     *
     * @return JsonResponse
     */
    public function viewChapterPdfAction(Chapter $chapter)
    {
        $lesson = $chapter->getLesson();

        $this->checkPermission('EXPORT', $lesson->getResourceNode(), [], true);

        return new JsonResponse([
            'name' => $lesson->getResourceNode()->getName().' - '.$chapter->getTitle(),
            'content' => $this->templating->render(
                'IcapLessonBundle:lesson:open.pdf.twig', [
                    '_resource' => $lesson,
                    'tree' => $this->chapterRepo->getChapterTree($chapter),
                ]
            ),
        ]);
    }
}
