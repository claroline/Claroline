<?php

namespace Icap\LessonBundle\Controller\Resource;

use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Library\Configuration\PlatformConfigurationHandler;
use Claroline\CoreBundle\Library\Normalizer\TextNormalizer;
use Claroline\CoreBundle\Security\PermissionCheckerTrait;
use Dompdf\Dompdf;
use Icap\LessonBundle\Entity\Chapter;
use Icap\LessonBundle\Entity\Lesson;
use Icap\LessonBundle\Repository\ChapterRepository;
use Sensio\Bundle\FrameworkExtraBundle\Configuration as EXT;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Bundle\TwigBundle\TwigEngine;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

class LessonController extends Controller
{
    use PermissionCheckerTrait;

    /** @var ObjectManager */
    private $om;
    /** @var PlatformConfigurationHandler */
    private $config;
    /** @var TwigEngine */
    private $templating;

    /** @var ChapterRepository */
    private $chapterRepo;

    /**
     * LessonController constructor.
     *
     * @param AuthorizationCheckerInterface $authorization
     * @param PlatformConfigurationHandler  $config
     * @param ObjectManager                 $om
     * @param TwigEngine                    $templating
     */
    public function __construct(
        AuthorizationCheckerInterface $authorization,
        PlatformConfigurationHandler $config,
        ObjectManager $om,
        TwigEngine $templating
    ) {
        $this->authorization = $authorization;
        $this->config = $config;
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
     * @return StreamedResponse
     */
    public function viewLessonPdfAction(Lesson $lesson)
    {
        $this->checkPermission('EXPORT', $lesson->getResourceNode(), [], true);

        $domPdf = new Dompdf();
        $domPdf->set_option('isHtml5ParserEnabled', true);
        $domPdf->set_option('isRemoteEnabled', true);
        $domPdf->set_option('tempDir', $this->config->getParameter('server.tmp_dir'));
        $domPdf->loadHtml($this->templating->render('IcapLessonBundle:lesson:open.pdf.twig', [
            '_resource' => $lesson,
            'tree' => $this->chapterRepo->getChapterTree($lesson->getRoot(), false),
        ]));

        // Render the HTML as PDF
        $domPdf->render();

        $fileName = TextNormalizer::toKey($lesson->getResourceNode()->getName());

        return new StreamedResponse(function () use ($domPdf, $fileName) {
            echo $domPdf->output();
        }, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename='.$fileName.'.pdf',
        ]);
    }

    /**
     * @EXT\Route("view/chapter/{chapter}.pdf", name="icap_lesson_chapter_export_pdf")
     * @EXT\ParamConverter("chapter", class="IcapLessonBundle:Chapter", options={"mapping": {"chapter": "uuid"}})
     *
     * @param Chapter $chapter
     *
     * @return StreamedResponse
     */
    public function viewChapterPdfAction(Chapter $chapter)
    {
        $lesson = $chapter->getLesson();

        $this->checkPermission('EXPORT', $lesson->getResourceNode(), [], true);

        $domPdf = new Dompdf();
        $domPdf->set_option('isHtml5ParserEnabled', true);
        $domPdf->set_option('isRemoteEnabled', true);
        $domPdf->set_option('tempDir', $this->config->getParameter('server.tmp_dir'));
        $domPdf->loadHtml($this->templating->render('IcapLessonBundle:lesson:open.pdf.twig', [
            '_resource' => $lesson,
            'tree' => $this->chapterRepo->getChapterTree($chapter),
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
