<?php

namespace ICAP\LessonBundle\Controller;

use ICAP\LessonBundle\Form\ChapterType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Claroline\CoreBundle\Library\Resource\ResourceCollection;
use ICAP\LessonBundle\Entity\Lesson;
use ICAP\LessonBundle\Entity\Chapter;
use ICAP\LessonBundle\Form\DeleteChapterType;

class LessonController extends Controller
{
    /**
     * @Route(
     *      "view/{resourceId}",
     *      name="icap_lesson",
     *      requirements={"resourceId" = "\d+"},
     *      defaults={"chapterId" = 0}
     * )
     * @Route(
     *      "view/{resourceId}/{chapterId}",
     *      name="icap_lesson_chapter",
     *      requirements={"resourceId" = "\d+", "chapterId" = "\d+"}
     * )
     * @Template()
     */
    public function viewChapterAction($resourceId, $chapterId)
    {
        $lesson = $this->findLesson($resourceId);

        $chapterRepository = $this->getDoctrine()->getManager()->getRepository('ICAPLessonBundle:Chapter');
        $chapters = $chapterRepository->findBy(array('lesson' => $lesson));

        $chapter = null;
        if ($chapterId == 0) {
            $chapter = count($chapters) > 0 ? $chapters[0] : null;
        } else {
            $chapter = $this->findChapter($lesson, $chapterId);
        }

        return array(
            'lesson' => $lesson,
            'chapters' => $chapters,
            'chapter' => $chapter,
            'workspace' => $lesson->getWorkspace(),
            'pathArray' => $lesson->getPathArray()
        );
    }

    /**
     * @Route(
     *      "edit/{resourceId}/{chapterId}",
     *      name="icap_lesson_edit_chapter",
     *      requirements={"resourceId" = "\d+", "chapterId" = "\d+"}
     * )
     * @Template()
     */
    public function editChapterAction($resourceId, $chapterId)
    {
        $lesson = $this->findLesson($resourceId);

        $chapter = $this->findChapter($lesson, $chapterId);

        $form = $this->createForm(new ChapterType(), $chapter);

        return array(
            'lesson' => $lesson,
            'chapter' => $chapter,
            'form' => $form->createView(),
            'workspace' => $lesson->getWorkspace(),
            'pathArray' => $lesson->getPathArray()
        );
    }

    /**
     * @Route(
     *      "update/{resourceId}/{chapterId}",
     *      name="icap_lesson_update_chapter",
     *      requirements={"resourceId" = "\d+", "chapterId" = "\d+"}
     * )
     * @Template("ICAPLessonBundle:Lesson:editChapter.html.twig")
     */
    public function updateChapterAction($resourceId, $chapterId)
    {
        $translator = $this->get('translator');

        $lesson = $this->findLesson($resourceId);
        $chapter = $this->findChapter($lesson, $chapterId);

        $form = $this->createForm(new ChapterType(), $chapter);
        $form->handleRequest($this->getRequest());
        if ($form->isValid()) {
            $chapterForm = $form->getData();
            $em = $this->getDoctrine()->getManager();
            $em->persist($chapterForm);
            $em->flush();

            $this->get('session')->getFlashBag()->add('success',$translator->trans('Your chapter has been modified', array(), 'icap_lesson'));
        } else {
            $this->get('session')->getFlashBag()->add('error',$translator->trans('Your chapter has not been modified',array(), 'icap_lesson'));
        }

        return array(
            'lesson' => $lesson,
            'chapter' => $chapter,
            'form' => $form->createView(),
            'workspace' => $lesson->getWorkspace(),
            'pathArray' => $lesson->getPathArray()
        );
    }

    /**
     * @Route(
     *      "confirm-delete/{resourceId}/{chapterId}",
     *      name="icap_lesson_confirm_delete_chapter",
     *      requirements={"resourceId" = "\d+", "chapterId" = "\d+"}
     * )
     * @Template()
     */
    public function confirmDeleteChapterAction($resourceId, $chapterId)
    {
        $lesson = $this->findLesson($resourceId);
        $chapter = $this->findChapter($lesson, $chapterId);

        $form = $this->createForm(new DeleteChapterType(), $chapter);
        $form->handleRequest($this->getRequest());

        return array(
            'lesson' => $lesson,
            'chapter' => $chapter,
            'form' => $form->createView(),
            'workspace' => $lesson->getWorkspace(),
            'pathArray' => $lesson->getPathArray()
        );
    }

    /**
     * @Route(
     *      "delete/{resourceId}/{chapterId}",
     *      name="icap_lesson_delete_chapter",
     *      requirements={"resourceId" = "\d+", "chapterId" = "\d+"}
     * )
     * @Template("ICAPLessonBundle:Lesson:confirmDeleteChapter.html.twig")
     */
    public function deleteChapterAction($resourceId, $chapterId)
    {
        $translator = $this->get('translator');

        $lesson = $this->findLesson($resourceId);
        $chapter = $this->findChapter($lesson, $chapterId);

        $form = $this->createForm(new DeleteChapterType(), $chapter);
        $form->handleRequest($this->getRequest());
        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->remove($chapter);
            $em->flush();
            $this->get('session')->getFlashBag()->add('success',$translator->trans('Your chapter has been deleted',array(), 'icap_lesson'));

            return $this->redirect($this->generateUrl('icap_lesson', array('resourceId' => $lesson->getId())));
        } else {
            $this->get('session')->getFlashBag()->add('error',$translator->trans('Your chapter has not been deleted',array(), 'icap_lesson'));
        }

        return array(
            'lesson' => $lesson,
            'chapter' => $chapter,
            'form' => $form->createView(),
            'workspace' => $lesson->getWorkspace(),
            'pathArray' => $lesson->getPathArray()
        );
    }

    /**
     * @Route(
     *      "new/{resourceId}",
     *      name="icap_lesson_new_chapter",
     *      requirements={"resourceId" = "\d+"}
     * )
     * @Template()
     */
    public function newChapterAction($resourceId)
    {
        $lesson = $this->findLesson($resourceId);

        $form = $this->createForm(new ChapterType(), null);

        return array(
            'lesson' => $lesson,
            'form' => $form->createView(),
            'workspace' => $lesson->getWorkspace(),
            'pathArray' => $lesson->getPathArray()
        );
    }

    /**
     * @Route(
     *      "add/{resourceId}",
     *      name="icap_lesson_add_chapter",
     *      requirements={"resourceId" = "\d+"}
     * )
     * @Template("ICAPLessonBundle:Lesson:newChapter.html.twig")
     */
    public function addChapterAction($resourceId)
    {
        $translator = $this->get('translator');

        $lesson = $this->findLesson($resourceId);

        $form = $this->createForm(new ChapterType(), null);
        $form->handleRequest($this->getRequest());
        if ($form->isValid()) {
            $chapter = $form->getData();
            $chapter->setLesson($lesson);

            $em = $this->getDoctrine()->getManager();
            $em->persist($chapter);
            $em->flush();

            $this->get('session')->getFlashBag()->add('success',$translator->trans('Your chapter has been added',array(), 'icap_lesson'));
            return $this->redirect($this->generateUrl('icap_lesson_chapter', array('resourceId' => $lesson->getId(), 'chapterId' => $chapter->getId())));
        } else {
            $this->get('session')->getFlashBag()->add('error',$translator->trans('Your chapter has not been added',array(), 'icap_lesson'));
        }

        return array(
            'lesson' => $lesson,
            'form' => $form->createView(),
            'workspace' => $lesson->getWorkspace(),
            'pathArray' => $lesson->getPathArray()
        );
    }

    private function findLesson($resourceId)
    {
        $lessonRepository = $this->getDoctrine()->getManager()->getRepository('ICAPLessonBundle:Lesson');
        $lesson = $lessonRepository->findOneBy(array('id' => $resourceId));
        if ($lesson === null) {
            throw new NotFoundHttpException();
        }

        return $lesson;
    }
    private function findChapter($lesson, $chapterId)
    {
        $chapterRepository = $this->getDoctrine()->getManager()->getRepository('ICAPLessonBundle:Chapter');
        $chapter = $chapterRepository->findOneBy(array('id' => $chapterId, 'lesson' => $lesson));
        if ($chapter === null) {
            throw new NotFoundHttpException();
        }

        return $chapter;
    }

}
