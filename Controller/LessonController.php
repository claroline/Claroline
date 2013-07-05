<?php

namespace ICAP\LessonBundle\Controller;

use ICAP\LessonBundle\Form\ChapterType;
use ICAP\LessonBundle\Form\MoveChapterType;
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
     * @param $resourceId, $chapterId
     * @return $lesson, $chapters, $chapter
     *
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
        $em = $this->getDoctrine()->getManager();
        $chapterRepository = $em->getRepository('ICAPLessonBundle:Chapter');

        $chapter = null;

        if ($chapterId != 0) {
            $chapter = $this->findChapter($lesson, $chapterId);
        } else {
            $chapter = $chapterRepository->findOneBy(array('lesson' => $lesson, 'root' => $lesson->getRoot()->getId(), 'left' => 2));
        }

        $query = $this->getDoctrine()->getManager()
            ->createQueryBuilder()
            ->select('node')
            ->from('ICAP\\LessonBundle\\Entity\\Chapter', 'node')
            ->orderBy('node.root, node.left', 'ASC')
            ->where('node.root = :rootId')
            ->setParameter('rootId', $lesson->getRoot()->getId())
            ->getQuery()
        ;
        $options = array('decorate' => false);
        $tree = $chapterRepository->buildTree($query->getArrayResult(), $options);

        return array(
            'lesson' => $lesson,
            'tree' => $tree,
            'chapter' => $chapter,
            'workspace' => $lesson->getWorkspace(),
            'pathArray' => $lesson->getPathArray()
        );
    }

    /**
     * Route affichant le formulaire à l'utilisateur lui permettant de modifier le chapitre
     * @param $resourceId, $chapterId
     * @return $lesson, $chapter, $form
     *
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
     * Route mettant à jour le chapitre modifié par l'utilisateur
     * @param $resourceId, $chapterId
     * @return $lesson, $chapter, $form
     *
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
     * Route affichant une page de confirmation de la suppression
     * @param $resourceId, $chapterId
     * @return $lesson, $chapter, $form
     *
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

        $chapterRepository = $this->getDoctrine()->getManager()->getRepository('ICAPLessonBundle:Chapter');
        $childrenChapter = $chapterRepository->childCount($chapter);

        $form = $this->createForm(new DeleteChapterType(), $chapter, array('hasChildren' => $childrenChapter > 0));
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
     * Route effaçant le chapitre de la base
     * @param $resourceId, $chapter
     * @return $lesson, $chapter, $form
     *
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

        if($form->isValid()){
            if ($form->get('children')->getData() == false) {
                $em = $this->getDoctrine()->getManager();
                $repo = $em->getRepository('ICAPLessonBundle:Chapter');
                $repo->removeFromTree($chapter);
                $em->clear();
                $em->flush();

                $this->get('session')->getFlashBag()->add('success',$translator->trans('Your chapter has been deleted but no subchapter',array(), 'icap_lesson'));

                return $this->redirect($this->generateUrl('icap_lesson', array('resourceId' => $lesson->getId())));

            } else {
                $em = $this->getDoctrine()->getManager();
                $em->remove($chapter);
                $em->flush();

                $this->get('session')->getFlashBag()->add('success',$translator->trans('Your chapter has been deleted',array(), 'icap_lesson'));

                return $this->redirect($this->generateUrl('icap_lesson', array('resourceId' => $lesson->getId())));

                }
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
     * Route affichant le formulaire de création d'un nouveau chapitre
     * @param $resourceId
     * @return $lesson, $form
     *
     * @Route(
     *      "new/{resourceId}",
     *      name="icap_lesson_new_chapter_without_parent",
     *      requirements={"resourceId" = "\d+"},
     *      defaults={"parentChapterId" = 0}
     * )
     *
     * @Route(
     *      "new/{resourceId}/{parentChapterId}",
     *      name="icap_lesson_new_chapter",
     *      requirements={"resourceId" = "\d+", "parentChapterId" = "\d+"}
     * )
     * @Template()
     */
    public function newChapterAction($resourceId, $parentChapterId)
    {
        $lesson = $this->findLesson($resourceId);
        $form = $this->createForm(new ChapterType(), null);

        if($parentChapterId == 0){
            $chapterParent = $lesson->getRoot();
        }
        else{
            $chapterParent = $this->findChapter($lesson, $parentChapterId);
        }

        return array(
            'lesson' => $lesson,
            'form' => $form->createView(),
            'chapterParent' => $chapterParent,
            'workspace' => $lesson->getWorkspace(),
            'pathArray' => $lesson->getPathArray()
        );
    }

    /**
     * Route ajoutant le chapitre à la base
     * @param $resourceId
     * @return $lesson, $form
     *
     * @Route(
     *      "add/{resourceId}/{parentChapterId}",
     *      name="icap_lesson_add_chapter",
     *      requirements={"resourceId" = "\d+", "parentChapterId" = "\d+"}
     * )
     * @Template("ICAPLessonBundle:Lesson:newChapter.html.twig")
     */
    public function addChapterAction($resourceId, $parentChapterId)
    {
        $translator = $this->get('translator');

        $lesson = $this->findLesson($resourceId);
        $chapterParent = $this->findChapter($lesson, $parentChapterId);

        $form = $this->createForm(new ChapterType(), null);
        $form->handleRequest($this->getRequest());

        if ($form->isValid()) {
            $chapter = $form->getData();
            $chapter->setLesson($lesson);

            $em = $this->getDoctrine()->getManager();
            $chapterRepository = $this->getDoctrine()->getManager()->getRepository('ICAPLessonBundle:Chapter');
            $chapterRepository->persistAsLastChildOf($chapter, $chapterParent);
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


    /**
     *
     * @Route(
     *      "choice-move/{resourceId}/{chapterId}",
     *      name="icap_lesson_choice_move_chapter",
     *      requirements={"resourceId" = "\d+", "chapterId" = "\d+"}
     * )
     * @Template()
     */
    public function choiceMoveChapterAction($resourceId, $chapterId)
    {
        $lesson = $this->findLesson($resourceId);
        $chapter = $this->findChapter($lesson, $chapterId);

        $em = $this->getDoctrine()->getManager();
        $repo = $em->getRepository('ICAPLessonBundle:Chapter');
        $chapters = $repo->children($lesson->getRoot());

        $form = $this->createForm(new MoveChapterType(), $chapter,  array('chapters' => $chapters));
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
     *
     * @Route(
     *      "move/{resourceId}/{chapterId}",
     *      name="icap_lesson_move_chapter",
     *      requirements={"resourceId" = "\d+", "chapterId" = "\d+"}
     * )
     * @Method("POST")
     * @Template("ICAPLessonBundle:Lesson:choiceMoveChapter.html.twig")
     */
    public function moveChapterAction($resourceId, $chapterId)
    {
        $translator = $this->get('translator');

        $lesson = $this->findLesson($resourceId);
        $chapter = $this->findChapter($lesson, $chapterId);
        $em = $this->getDoctrine()->getManager();
        $repo = $em->getRepository('ICAPLessonBundle:Chapter');
        $chapters = $repo->children($lesson->getRoot());

        $form = $this->createForm(new MoveChapterType(), $chapter,  array('chapters' => $chapters));
        $form->handleRequest($this->getRequest());

        if ($form->isValid()) {
            $newParentId = $form->get('choiceChapter')->getData();
            $newParent = $this->findChapter($lesson, $newParentId);

            $path = $repo->getPath($newParent);
            foreach ($path as $currentParent) {
                if ($currentParent->getId() == $chapterId) {
                    throw new \InvalidArgumentException();
                }
            }
            if ($form->get('brother')->getData() == true){
                $repo->persistAsNextSiblingOf($chapter, $newParent);
                $em->flush();
            } else {
                $repo->persistAsLastChildOf($chapter, $newParent);
                $em->flush();
            }
            return $this->redirect($this->generateUrl('icap_lesson', array('resourceId' => $lesson->getId())));
        }

        return array(
            'lesson' => $lesson,
            'chapter' => $chapter,
            'form' => $form->createView(),
            'workspace' => $lesson->getWorkspace(),
            'pathArray' => $lesson->getPathArray()
        );
    }

    /*
     * fonction recherchant un cours dans la base
     */
    private function findLesson($resourceId)
    {
        $lessonRepository = $this->getDoctrine()->getManager()->getRepository('ICAPLessonBundle:Lesson');
        $lesson = $lessonRepository->findOneBy(array('id' => $resourceId));
        if ($lesson === null) {
            throw new NotFoundHttpException();
        }

        return $lesson;
    }

    /*
     * fonction recherchant un chapitre dans la base
     */
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
