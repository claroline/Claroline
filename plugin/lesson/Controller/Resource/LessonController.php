<?php

namespace Icap\LessonBundle\Controller\Resource;

use Claroline\CoreBundle\Security\PermissionCheckerTrait;
use Icap\LessonBundle\Entity\Lesson;
use Sensio\Bundle\FrameworkExtraBundle\Configuration as EXT;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;

class LessonController extends Controller
{
    use PermissionCheckerTrait;

    /**
     * @EXT\Route(
     *      "view/{lesson}.pdf",
     *      name="icap_lesson_export_pdf",
     *      requirements={"resourceId" = "\d+"}
     * )
     * @EXT\ParamConverter("lesson", class="IcapLessonBundle:Lesson", options={"mapping": {"lesson": "uuid"}})
     */
    public function viewLessonPdfAction(Lesson $lesson)
    {
        $this->checkPermission('EXPORT', $lesson->getResourceNode(), [], true);

        $chapterRepository = $this->getDoctrine()->getManager()->getRepository('IcapLessonBundle:Chapter');
        $tree = $chapterRepository->buildChapterTree($lesson->getRoot());
        $content = $this->renderView(
            'IcapLessonBundle:lesson:open.pdf.twig', [
            '_resource' => $lesson,
            'tree' => $tree,
                ]
        );

        return new Response(
                $this->get('knp_snappy.pdf')->getOutputFromHtml(
                        $content, [
                    'outline' => true,
                    'footer-right' => '[page]/[toPage]',
                    'footer-spacing' => 3,
                    'footer-font-size' => 8,
                        ], true
                ), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="'.$lesson->getResourceNode()->getName().'.pdf"',
                ]
        );
    }
}
