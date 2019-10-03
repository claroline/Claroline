<?php

namespace Icap\LessonBundle\Controller\Resource;

use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Security\PermissionCheckerTrait;
use Icap\LessonBundle\Entity\Lesson;
use Sensio\Bundle\FrameworkExtraBundle\Configuration as EXT;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Bundle\TwigBundle\TwigEngine;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

class LessonController extends Controller
{
    use PermissionCheckerTrait;

    public function __construct(
        AuthorizationCheckerInterface $authorization,
        ObjectManager $om,
        TwigEngine $templating
    ) {
        $this->authorization = $authorization;
        $this->om = $om;
        $this->templating = $templating;
    }

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

        $chapterRepository = $this->om->getRepository('IcapLessonBundle:Chapter');
        $tree = $chapterRepository->buildChapterTree($lesson->getRoot());
        $content = $this->templating->render(
            'IcapLessonBundle:lesson:open.pdf.twig', [
            '_resource' => $lesson,
            'tree' => $tree,
                ]
        );

        return new JsonResponse([
          'content' => $content,
          'name' => $lesson->getResourceNode()->getName(),
        ]);
    }
}
