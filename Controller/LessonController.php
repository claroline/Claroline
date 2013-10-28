<?php

namespace Icap\LessonBundle\Controller;

use Icap\LessonBundle\Form\ChapterType;
use Icap\LessonBundle\Form\MoveChapterType;
use Icap\LessonBundle\Form\DuplicateChapterType;
use Icap\LessonBundle\Event\Log\LogChapterReadEvent;
use Icap\LessonBundle\Event\Log\LogChapterUpdateEvent;
use Icap\LessonBundle\Event\Log\LogChapterCreateEvent;
use Icap\LessonBundle\Event\Log\LogChapterDeleteEvent;
use Icap\LessonBundle\Event\Log\LogChapterMoveEvent;
use Claroline\CoreBundle\Event\Log\LogResourceReadEvent;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Claroline\CoreBundle\Library\Resource\ResourceCollection;
use Icap\LessonBundle\Entity\Lesson;
use Icap\LessonBundle\Entity\Chapter;
use Icap\LessonBundle\Form\DeleteChapterType;

class LessonController extends Controller
{

    /**
     * @param string $permission
     *
     * @param Blog $blog
     *
     * @throws AccessDeniedException
     */
    protected function checkAccess($permission, Lesson $lesson)
    {
        $collection = new ResourceCollection(array($lesson->getResourceNode()));
        if (!$this->get('security.context')->isGranted($permission, $collection)) {
            throw new AccessDeniedException($collection->getErrorsForDisplay());
        }

        $logEvent = new LogResourceReadEvent($lesson->getResourceNode());
        $this->get('event_dispatcher')->dispatch('log', $logEvent);
    }

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
     * @ParamConverter("lesson", class="IcapLessonBundle:Lesson", options={"id" = "resourceId"})
     * @Template()
     */
    public function viewChapterAction($lesson, $chapterId)
    {
        $this->checkAccess("OPEN", $lesson);

        $chapterRepository = $this->getDoctrine()->getManager()->getRepository('IcapLessonBundle:Chapter');
        $chapter = null;
        $parent = null;
        $path = null;
        if ($chapterId != 0) {
            $chapter = $this->findChapter($lesson, $chapterId);
            $parent = $chapter;
            $path = $chapterRepository->getPath($chapter);
            //path first element is the lesson root, we don't show it in the breadcrumb
            unset($path[0]);
        } else {
            $chapter = $chapterRepository->getFirstChapter($lesson);
            $parent = $lesson->getRoot();
        }
        //get complete chapter tree for this lesson
        $tree = $chapterRepository->getChapterTree($lesson->getRoot());

        //form used to move chapters, used by dragndrop methods
        $chapters = $chapterRepository->getChapterAndChapterChildren($lesson->getRoot());
        //$form = $this->createForm(new MoveChapterType(), $chapter, array('chapters' => $chapters));
        $form = $this->createForm($this->get("icap.lesson.movechaptertype"), $chapter);

        //the first time you enter the lesson there's no chapter
        if($chapter != null){
            $this->dispatchChapterReadEvent($lesson, $chapter);
            $res = array();
            //$this->parseNode($tree[0], $chapter, $res);
            $previous = $chapterRepository->getPreviousChapter($chapter);
            $next = $chapterRepository->getNextChapter($chapter);
            if($previous == null){
                $previous = $chapter;
            }
            if($next == null){
                $next = $chapter;
            }
            //var_dump($previous->getId());
            //var_dump($next->getId());
        }

        return array(
            '_resource'         => $lesson,
            'node'              => new ResourceCollection(array($lesson->getResourceNode())),
            'tree'              => $tree[0],
            'parent'            => $parent,
            'chapter'           => $chapter,
            'form'              => $form->createView(),
            'previous'          => $previous->getId(),
            'next'              => $next->getId(),
            'workspace'         => $lesson->getResourceNode()->getWorkspace()
        );
    }

    public function parseTree($collection, $chapterTarget, &$res, $previous, $getnext){
        foreach ($collection as $node) {
            return $this->parseNode($node, $chapterTarget, $res, $previous, $getnext);
        }
    }

    public function parseNode($node, $chapterTarget, &$res, $previous = null, $getnext = false){
        var_dump($getnext);
        var_dump($node["title"]."<br/>");

        $tmp = null;
        if($previous == null){
            $previous = $chapterTarget->getId();
        }
        if($node["id"] == $chapterTarget->getId()){
            $res["previous"] = $previous;
            $getnext = true;
        }else if($getnext){
            $res["next"] = $node["id"];
            //return true;
        }
        $previous = $node["id"];
        if (isset($node["__children"])){
            $getnext = $this->parseTree($node["__children"], $chapterTarget, $res, $previous, $getnext);
        }
        return $getnext;

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
     * @ParamConverter("lesson", class="IcapLessonBundle:Lesson", options={"id" = "resourceId"})
     * @Template()
     */
    public function editChapterAction($lesson, $chapterId)
    {
        $this->checkAccess("EDIT", $lesson);

        $chapter = $this->findChapter($lesson, $chapterId);
        $form = $this->createForm(new ChapterType(), $chapter);
        //for ajaxification
        if ($this->getRequest()->isXMLHttpRequest()) {
            return $this->render(
                'IcapLessonBundle:Lesson:editChapterAjaxified.html.twig',
                array(
                    '_resource' => $lesson,
                    'chapter' => $chapter,
                    'form' => $form->createView(),
                    'workspace' => $lesson->getResourceNode()->getWorkspace()
                )
            );
        }

        return array(
            '_resource' => $lesson,
            'chapter' => $chapter,
            'form' => $form->createView(),
            'workspace' => $lesson->getResourceNode()->getWorkspace()
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
     * @ParamConverter("lesson", class="IcapLessonBundle:Lesson", options={"id" = "resourceId"})
     * @Template("IcapLessonBundle:Lesson:editChapter.html.twig")
     */
    public function updateChapterAction($lesson, $chapterId)
    {
        $this->checkAccess("EDIT", $lesson);
        $translator = $this->get('translator');

        $chapter = $this->findChapter($lesson, $chapterId);

        $form = $this->createForm(new ChapterType(), $chapter);
        $form->handleRequest($this->getRequest());
        if ($form->isValid()) {
            try {
                $em = $this->getDoctrine()->getManager();
                $unitOfWork = $em->getUnitOfWork();
                $unitOfWork->computeChangeSets();
                $changeSet = $unitOfWork->getEntityChangeSet($chapter);

                $em->persist($chapter);
                $em->flush();

                $this->dispatchChapterUpdateEvent($lesson, $chapter, $changeSet);
            } catch (\Exception $exception) {
                $this->get('session')->getFlashBag()->add('error',$translator->trans('Your chapter has not been modified',array(), 'icap_lesson'));
            }
            $this->get('session')->getFlashBag()->add('success',$translator->trans('Your chapter has been modified', array(), 'icap_lesson'));
        } else {
            $this->get('session')->getFlashBag()->add('error',$translator->trans('Your chapter has not been modified',array(), 'icap_lesson'));
        }
        return($this->redirect($this->generateUrl('icap_lesson_chapter', array(
            'resourceId' => $lesson->getId(),
            'chapterId' => $chapterId
        ))));
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
     * @ParamConverter("lesson", class="IcapLessonBundle:Lesson", options={"id" = "resourceId"})
     * @Template()
     */
    public function confirmDeleteChapterAction($lesson, $chapterId)
    {
        $this->checkAccess("EDIT", $lesson);
        $chapter = $this->findChapter($lesson, $chapterId);

        $chapterRepository = $this->getDoctrine()->getManager()->getRepository('IcapLessonBundle:Chapter');
        $childrenChapter = $chapterRepository->childCount($chapter);

        $form = $this->createForm(new DeleteChapterType(), $chapter, array('hasChildren' => $childrenChapter > 0));
        $form->handleRequest($this->getRequest());

        //for ajaxification
        if ($this->getRequest()->isXMLHttpRequest()) {
            return $this->render(
                'IcapLessonBundle:Lesson:deleteChapterPopup.html.twig',
                array(
                    'lesson' => $lesson,
                    'chapter' => $chapter,
                    'form' => $form->createView(),
                    'haschild' => $childrenChapter,
                    'workspace' => $lesson->getResourceNode()->getWorkspace()
                )
            );
        }
        return array(
            'lesson' => $lesson,
            'chapter' => $chapter,
            'form' => $form->createView(),
            'haschild' => $childrenChapter,
            'workspace' => $lesson->getResourceNode()->getWorkspace()
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
     * @ParamConverter("lesson", class="IcapLessonBundle:Lesson", options={"id" = "resourceId"})
     * @Template("IcapLessonBundle:Lesson:confirmDeleteChapter.html.twig")
     */
    public function deleteChapterAction($lesson, $chapterId)
    {
        $this->checkAccess("EDIT", $lesson);
        $translator = $this->get('translator');

        $chapter = $this->findChapter($lesson, $chapterId);

        $form = $this->createForm(new DeleteChapterType(), $chapter);
        $form->handleRequest($this->getRequest());

        if($form->isValid()){
            $chaptername = $chapter->getTitle();
            $deleteChildren = false;
            if($form->has('deletechildren')){
                $deleteChildren = $form->get('deletechildren')->getData();
            }

            $em = $this->getDoctrine()->getManager();
            if ($deleteChildren) {
                $em->remove($chapter);
                $em->flush();
                $this->get('session')->getFlashBag()->add('success',$translator->trans('Your chapter has been deleted',array(), 'icap_lesson'));
            }
            else
            {
                $repo = $em->getRepository('IcapLessonBundle:Chapter');
                $repo->removeFromTree($chapter);
                //$em->clear();
                $em->flush();
                $this->get('session')->getFlashBag()->add('success',$translator->trans('Your chapter has been deleted but no subchapter',array(), 'icap_lesson'));
            }
            $this->dispatchChapterDeleteEvent($lesson, $chaptername);
            return $this->redirect($this->generateUrl('icap_lesson', array('resourceId' => $lesson->getId())));
        } else {
            $this->get('session')->getFlashBag()->add('error',$translator->trans('Your chapter has not been deleted',array(), 'icap_lesson'));
        }
        return array(
            'lesson' => $lesson,
            'chapter' => $chapter,
            'form' => $form->createView(),
            'workspace' => $lesson->getResourceNode()->getWorkspace()
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
     * @ParamConverter("lesson", class="IcapLessonBundle:Lesson", options={"id" = "resourceId"})
     * @Template()
     */
    public function newChapterAction($lesson, $parentChapterId)
    {
        $this->checkAccess("EDIT", $lesson);
        $form = $this->createForm(new ChapterType(), null);

        if($parentChapterId == 0){
            $chapterParent = $lesson->getRoot();
        }
        else{
            $chapterParent = $this->findChapter($lesson, $parentChapterId);
        }

        //for ajaxification
        if ($this->getRequest()->isXMLHttpRequest()) {
            return $this->render(
                'IcapLessonBundle:Lesson:newChapterAjaxified.html.twig',
                array(
                    '_resource' => $lesson,
                    'form' => $form->createView(),
                    'chapterParent' => $chapterParent,
                    'workspace' => $lesson->getResourceNode()->getWorkspace()
                )
            );
        }

        return array(
            '_resource' => $lesson,
            'form' => $form->createView(),
            'chapterParent' => $chapterParent,
            'workspace' => $lesson->getResourceNode()->getWorkspace()
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
     * @Template("IcapLessonBundle:Lesson:newChapter.html.twig")
     * @ParamConverter("lesson", class="IcapLessonBundle:Lesson", options={"id" = "resourceId"})
     */
    public function addChapterAction($lesson, $parentChapterId)
    {
        $this->checkAccess("EDIT", $lesson);
        $translator = $this->get('translator');

        $chapterParent = $this->findChapter($lesson, $parentChapterId);

        $form = $this->createForm(new ChapterType(), null);
        $form->handleRequest($this->getRequest());

        if ($form->isValid()) {
            $chapter = $form->getData();
            $chapter->setLesson($lesson);

            $em = $this->getDoctrine()->getManager();
            $chapterRepository = $this->getDoctrine()->getManager()->getRepository('IcapLessonBundle:Chapter');
            $chapterRepository->persistAsLastChildOf($chapter, $chapterParent);
            $em->flush();

            $this->dispatchChapterCreateEvent($lesson, $chapter);

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
     * @ParamConverter("lesson", class="IcapLessonBundle:Lesson", options={"id" = "resourceId"})
     */
    public function choiceMoveChapterAction($lesson, $chapterId)
    {
        $this->checkAccess("EDIT", $lesson);
        $chapter = $this->findChapter($lesson, $chapterId);

        $em = $this->getDoctrine()->getManager();
        $repo = $em->getRepository('IcapLessonBundle:Chapter');

        //retrieve current chapter and its children, current chapter cant be dropped in those
        $nonLegitTargets =  $repo->getChapterAndChapterChildren($chapter);
        $chapters = $repo->getChapterAndChapterChildren($lesson->getRoot());
        //remove $nonLegitTargets from $chapters
        foreach ($chapters as $key => $chap) {
            foreach ($nonLegitTargets as $key2 => $chap2) {
                if($chap->getId() == $chap2->getId()){
                    unset($chapters[$key]);
                }
            }
        }

        //$form = $this->createForm(new MoveChapterType(), $chapter,  array('chapters' => $chapters));
        $form = $this->createForm($this->get("icap.lesson.movechaptertype"), $chapter);
        $form->handleRequest($this->getRequest());

        //for ajaxification
        if ($this->getRequest()->isXMLHttpRequest()) {
            return $this->render(
                'IcapLessonBundle:Lesson:choiceMoveChapterAjaxified.html.twig',
                array(
                    '_resource' => $lesson,
                    'chapter' => $chapter,
                    'form' => $form->createView(),
                    'workspace' => $lesson->getResourceNode()->getWorkspace()
                )
            );
        }

        return array(
            '_resource' => $lesson,
            'chapter' => $chapter,
            'form' => $form->createView(),
            'workspace' => $lesson->getResourceNode()->getWorkspace()
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
     * @Template("IcapLessonBundle:Lesson:choiceMoveChapter.html.twig")
     * @ParamConverter("lesson", class="IcapLessonBundle:Lesson", options={"id" = "resourceId"})
     */
    public function moveChapterAction($lesson, $chapterId)
    {
        $this->checkAccess("EDIT", $lesson);
        $translator = $this->get('translator');

        $chapter = $this->findChapter($lesson, $chapterId);
        $oldparent = $chapter->getParent();
        $em = $this->getDoctrine()->getManager();
        $repo = $em->getRepository('IcapLessonBundle:Chapter');

        $chapters =  $repo->getChapterAndChapterChildren($lesson->getRoot());

        //$form = $this->createForm(new MoveChapterType(), $chapter,  array('chapters' => $chapters));
        $form = $this->createForm($this->get("icap.lesson.movechaptertype"), $chapter);
        $form->handleRequest($this->getRequest());
        if ($form->isValid() and $form->get('choiceChapter')->getData() != $chapter->getid()) {
            $newParentId = $form->get('choiceChapter')->getData();
            $brother = $form->get('brother')->getData();
            $firstposition = $form->get('firstposition')->getData();
        }else{
            return array(
                'lesson' => $lesson,
                'chapter' => $chapter,
                'form' => $form->createView(),
                'workspace' => $lesson->getResourceNode()->getWorkspace()
            );
        }

        $newParent = $this->findChapter($lesson, $newParentId);
        $path = $repo->getPath($newParent);
        foreach ($path as $currentParent) {
            if ($currentParent->getId() == $chapterId) {
                throw new \InvalidArgumentException();
            }
        }

        //a node cant be sibling with root
/*        var_dump($firstposition);
        die();*/
        if ($brother == true and $newParentId != $lesson->getRoot()->getId()){
            $repo->persistAsNextSiblingOf($chapter, $newParent);
        } else {
            if($firstposition == "true"){
                $repo->persistAsFirstChildOf($chapter, $newParent);
            }else{
                $repo->persistAsLastChildOf($chapter, $newParent);
            }
        }
        $em->flush();

        $this->dispatchChapterMoveEvent($lesson, $chapter, $oldparent, $newParent);

        return($this->redirect($this->generateUrl('icap_lesson_chapter', array(
            'resourceId' => $lesson->getId(),
            'chapterId' => $chapterId
        ))));
    }

    /**
     *
     * @Route(
     *      "duplicate_form/{resourceId}/{chapterId}",
     *      name="icap_lesson_duplicate_form_chapter",
     *      requirements={"resourceId" = "\d+", "chapterId" = "\d+"}
     * )
     * @Template("IcapLessonBundle:Lesson:duplicateChapter.html.twig")
     * @ParamConverter("lesson", class="IcapLessonBundle:Lesson", options={"id" = "resourceId"})
     */
    public function duplicateFormChapterAction($lesson, $chapterId)
    {
        $this->checkAccess("EDIT", $lesson);
        $chapter = $this->findChapter($lesson, $chapterId);

        $em = $this->getDoctrine()->getManager();
        $repo = $em->getRepository('IcapLessonBundle:Chapter');

        $form = $this->createForm($this->get("icap.lesson.duplicatechaptertype"), $chapter);
        $form->handleRequest($this->getRequest());

        //for ajaxification
        if ($this->getRequest()->isXMLHttpRequest()) {
            return $this->render(
                'IcapLessonBundle:Lesson:duplicateChapterAjaxified.html.twig',
                array(
                    '_resource' => $lesson,
                    'chapter' => $chapter,
                    'form' => $form->createView(),
                    'workspace' => $lesson->getResourceNode()->getWorkspace()
                )
            );
        }

        return array(
            '_resource' => $lesson,
            'chapter' => $chapter,
            'form' => $form->createView(),
            'workspace' => $lesson->getResourceNode()->getWorkspace()
        );
    }

    /**
     *
     * @Route(
     *      "duplicate/{resourceId}/{chapterId}",
     *      name="icap_lesson_duplicate_chapter",
     *      requirements={"resourceId" = "\d+", "chapterId" = "\d+"}
     * )
     * @Template()
     * @ParamConverter("lesson", class="IcapLessonBundle:Lesson", options={"id" = "resourceId"})
     * @ParamConverter("chapter", class="IcapLessonBundle:Chapter", options={"id" = "chapterId"})
     */
    public function duplicateChapterAction($lesson, $chapter)
    {
        $this->checkAccess("EDIT", $lesson);

        $chapter_manager = $this->container->get("icap.lesson.manager.chapter");
        $em = $this->getDoctrine()->getManager();
        $repo = $em->getRepository('IcapLessonBundle:Chapter');

        $form = $this->createForm($this->get("icap.lesson.duplicatechaptertype"), $chapter);
        $form->handleRequest($this->getRequest());
        $parent = null;
        $copy_children = false;
        if ($form->isValid()) {
            $parent = $this->findChapter($lesson, $form->get('parent')->getData());
            if($form->has('duplicate_children')){
                $copy_children = $form->get('duplicate_children')->getData();
            }
        }else{
            return (
                $this->redirect(
                    $this->generateUrl(
                        'icap_lesson_duplicate_form_chapter',
                        array(
                            'resourceId' => $lesson->getId(),
                            'chapterId' => $chapter->getId()
                        )
                    )
                ));
        }

        $chapter_copy = $chapter_manager->copyChapter($chapter, $parent, $copy_children, true);
        $em->flush();

        $this->dispatchChapterCreateEvent($lesson, $chapter_copy);

        return($this->redirect($this->generateUrl('icap_lesson_chapter', array(
            'resourceId' => $lesson->getId(),
            'chapterId' => $chapter_copy->getId()
        ))));
    }

    /*
     * fonction recherchant un cours dans la base
     */
    private function findLesson($resourceId)
    {
        $lessonRepository = $this->getDoctrine()->getManager()->getRepository('IcapLessonBundle:Lesson');
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
        $chapterRepository = $this->getDoctrine()->getManager()->getRepository('IcapLessonBundle:Chapter');
        $chapter = $chapterRepository->findOneBy(array('id' => $chapterId, 'lesson' => $lesson));
        if ($chapter === null) {
            throw new NotFoundHttpException();
        }

        return $chapter;
    }

    /**
     * @param Lesson  $lesson
     * @param Chapter  $chapter
     * @return Controller
     */
    protected function dispatchChapterReadEvent(Lesson $lesson, Chapter $chapter)
    {
        $event = new LogChapterReadEvent($lesson, $chapter);
        return  $this->get('event_dispatcher')->dispatch('log', $event);
    }

    /**
     * @param Lesson  $lesson
     * @param Chapter  $chapter
     * @param $changeSet
     * @return Controller
     */
    protected function dispatchChapterUpdateEvent(Lesson $lesson, Chapter $chapter, $changeSet)
    {
        $event = new LogChapterUpdateEvent($lesson, $chapter, $changeSet);
        return  $this->get('event_dispatcher')->dispatch('log', $event);
    }

    /**
     * @param Lesson  $lesson
     * @param Chapter  $chapter
     * @return Controller
     */
    protected function dispatchChapterCreateEvent(Lesson $lesson, Chapter $chapter)
    {
        $event = new LogChapterCreateEvent($lesson, $chapter);
        return  $this->get('event_dispatcher')->dispatch('log', $event);
    }

    /**
     * @param Lesson  $lesson
     * @param Chapter  $chapter
     * @return Controller
     */
    protected function dispatchChapterDeleteEvent(Lesson $lesson, $chaptername)
    {
        $event = new LogChapterDeleteEvent($lesson, $chaptername);
        return  $this->get('event_dispatcher')->dispatch('log', $event);
    }

    /**
     * @param Lesson  $lesson
     * @param Chapter  $chapter
     * @return Controller
     */
    protected function dispatchChapterMoveEvent(Lesson $lesson, Chapter $chapter, Chapter $oldchapter, Chapter $newchapter)
    {
        $event = new LogChapterMoveEvent($lesson, $chapter, $oldchapter, $newchapter);
        return  $this->get('event_dispatcher')->dispatch('log', $event);
    }

}
