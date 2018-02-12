<?php

namespace Icap\LessonBundle\Controller;

use Claroline\CoreBundle\Event\Log\LogResourceReadEvent;
use Claroline\CoreBundle\Library\Resource\ResourceCollection;
use Icap\LessonBundle\Entity\Chapter;
use Icap\LessonBundle\Entity\Lesson;
use Icap\LessonBundle\Event\Log\LogChapterCreateEvent;
use Icap\LessonBundle\Event\Log\LogChapterDeleteEvent;
use Icap\LessonBundle\Event\Log\LogChapterMoveEvent;
use Icap\LessonBundle\Event\Log\LogChapterReadEvent;
use Icap\LessonBundle\Event\Log\LogChapterUpdateEvent;
use Icap\LessonBundle\Form\DeleteChapterType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class LessonController extends Controller
{
    /**
     * @param string $permission
     * @param Lesson $lesson
     *
     * @throws AccessDeniedException
     */
    protected function checkAccess($permission, Lesson $lesson)
    {
        $collection = new ResourceCollection([$lesson->getResourceNode()]);
        if (!$this->get('security.authorization_checker')->isGranted($permission, $collection)) {
            throw new AccessDeniedException($collection->getErrorsForDisplay());
        }

        $logEvent = new LogResourceReadEvent($lesson->getResourceNode());
        $this->get('event_dispatcher')->dispatch('log', $logEvent);
    }

    /**
     * @Route(
     *      "view/{resourceId}/",
     *      name="icap_lesson",
     *      requirements={"resourceId" = "\d+"}
     * )
     * @ParamConverter("lesson", class="IcapLessonBundle:Lesson", options={"id" = "resourceId"})
     * @Template("IcapLessonBundle:Lesson:viewChapter.html.ng.twig")
     */
    public function viewLessonAction($lesson)
    {
        $this->checkAccess('OPEN', $lesson);

        return $this->getChapterView($lesson, $this->getDoctrine()->getManager()->getRepository('IcapLessonBundle:Chapter')->getFirstChapter($lesson));
    }

    /**
     * @Route(
     *      "view/pdf/{resourceId}.{_format}",
     *      name="icap_lesson_pdf",
     *      requirements={"resourceId" = "\d+", "_format" = "pdf"},
     *      defaults={"_format" = "pdf"}
     * )
     * @ParamConverter("lesson", class="IcapLessonBundle:Lesson", options={"id" = "resourceId"})
     */
    public function viewLessonPdfAction(Lesson $lesson)
    {
        $this->checkAccess('OPEN', $lesson);
        $chapterRepository = $this->getDoctrine()->getManager()->getRepository('IcapLessonBundle:Chapter');
        $tree = $chapterRepository->buildChapterTree($lesson->getRoot());
        $content = $this->renderView(
                'IcapLessonBundle:Lesson:view.pdf.twig', [
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

    /**
     * @Route(
     *      "view/{resourceId}/{chapterId}",
     *      name = "icap_lesson_chapter",
     *      requirements = {"resourceId" = "\d+"},
     *      options = {"expose"=true}
     * )
     * @ParamConverter("lesson", class="IcapLessonBundle:Lesson", options={"id" = "resourceId"})
     * @Template("IcapLessonBundle:Lesson:viewChapter.html.twig")
     */
    public function viewChapterAction($lesson, $chapterId)
    {
        $this->checkAccess('OPEN', $lesson);
        $chapter = null;
        //ugly fix for compliance with old permalinks using chapter ID
        if (is_numeric($chapterId)) {
            $chapter = $this->getDoctrine()->getManager()->getRepository('IcapLessonBundle:Chapter')->getChapterById($chapterId, $lesson->getId());
        }
        if ($chapter === null) {
            $chapter = $this->getDoctrine()->getManager()->getRepository('IcapLessonBundle:Chapter')->getChapterBySlug($chapterId, $lesson->getId());
        }

        if (null === $chapter) {
            throw $this->createNotFoundException('Chapter not found.');
        }

        return $this->getChapterView($lesson, $chapter);
    }

    private function getChapterView($lesson, $chapter)
    {
        //the first time you enter the lesson there's no chapter
        $previousChapterId = null;
        $previousChapterSlug = null;
        $nextChapterId = null;
        $nextChapterSlug = null;
        $tree = null;
        $form_view = null;
        $parent = $lesson->getRoot();
        $defaultChapter = null;
        $chapterRepository = $this->getDoctrine()->getManager()->getRepository('IcapLessonBundle:Chapter');

        if ($chapter !== null) {
            //get complete chapter tree for this lesson
            $tmp_tree = $chapterRepository->getChapterTree($lesson->getRoot());
            $tree = $tmp_tree[0];
            //form used to move chapters, used by dragndrop methods
            $form_view = $this->createForm($this->get('icap.lesson.movechaptertype'), $chapter, ['attr' => ['filter' => 0]])->createView();
            $path = $chapterRepository->getPath($chapter);
            //path first element is the lesson root, we don't show it in the breadcrumb
            unset($path[0]);
            $this->dispatchChapterReadEvent($lesson, $chapter);
            $previous = $chapterRepository->getPreviousChapter($chapter);
            if ($previous !== null) {
                $previousChapterId = $previous->getId();
                $previousChapterSlug = $previous->getSlug();
            }
            $next = $chapterRepository->getNextChapter($chapter);
            if ($next !== null) {
                $nextChapterId = $next->getId();
                $nextChapterSlug = $next->getSlug();
            }
            $parent = $chapter;

            $defaultChapter = $chapterRepository->getFirstChapter($lesson)->getSlug();
        }

        return [
            '_resource' => $lesson,
            'node' => new ResourceCollection([$lesson->getResourceNode()]),
            'tree' => $tree,
            'parent' => $parent,
            'chapter' => $chapter,
            'form' => $form_view,
            'previous' => $previousChapterSlug,
            'next' => $nextChapterSlug,
            'workspace' => $lesson->getResourceNode()->getWorkspace(),
            'chapters' => $chapterRepository->getChapterAndChapterChildren($lesson->getRoot()),
            'root' => $lesson->getRoot(),
            'defaultChapter' => $defaultChapter,
        ];
    }

    /**
     * Route affichant le formulaire à l'utilisateur lui permettant de modifier le chapitre.
     *
     * @param $resourceId, $chapterId
     *
     * @return $lesson, $chapter, $form
     *
     * @Route(
     *      "edit/{resourceId}/{chapterId}",
     *      name="icap_lesson_edit_chapter",
     *      requirements={"resourceId" = "\d+", "chapterId" = "\d+"},
     *      options = {"expose"=true}
     * )
     * @ParamConverter("lesson", class="IcapLessonBundle:Lesson", options={"id" = "resourceId"})
     * @Template()
     */
    public function editChapterAction($lesson, $chapterId)
    {
        $this->checkAccess('EDIT', $lesson);

        $chapter = $this->findChapter($lesson, $chapterId);
        $form = $this->createForm($this->get('icap.lesson.chaptertype'), $chapter);
        //$form = $this->createForm(new ChapterType(), $chapter);
        //for ajaxification
        if ($this->getRequest()->isXMLHttpRequest()) {
            return $this->render(
                            'IcapLessonBundle:Lesson:editChapterAjaxified.html.twig', [
                        '_resource' => $lesson,
                        'chapter' => $chapter,
                        'form' => $form->createView(),
                        'workspace' => $lesson->getResourceNode()->getWorkspace(),
                            ]
            );
        }

        return [
            '_resource' => $lesson,
            'chapter' => $chapter,
            'form' => $form->createView(),
            'workspace' => $lesson->getResourceNode()->getWorkspace(),
        ];
    }

    /**
     * Route mettant à jour le chapitre modifié par l'utilisateur.
     *
     * @param $resourceId, $chapterId
     *
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
        $this->checkAccess('EDIT', $lesson);
        $translator = $this->get('translator');

        $chapter = $this->findChapter($lesson, $chapterId);

        $form = $this->createForm($this->get('icap.lesson.chaptertype'), $chapter);
        $form->handleRequest($this->getRequest());
        if ($form->isValid()) {
            try {
                $em = $this->getDoctrine()->getManager();
                $unitOfWork = $em->getUnitOfWork();
                $unitOfWork->computeChangeSets();
                $changeSet = $unitOfWork->getEntityChangeSet($chapter);
                $em->flush();

                $this->dispatchChapterUpdateEvent($lesson, $chapter, $changeSet);
            } catch (\Exception $exception) {
                $this->get('session')->getFlashBag()->add('error', $translator->trans('Your chapter has not been modified', [], 'icap_lesson'));
            }
            $this->get('session')->getFlashBag()->add('success', $translator->trans('Your chapter has been modified', [], 'icap_lesson'));
        } else {
            $this->get('session')->getFlashBag()->add('error', $translator->trans('Your chapter has not been modified', [], 'icap_lesson'));
        }

        return $this->redirect($this->generateUrl('icap_lesson_chapter', [
                            'resourceId' => $lesson->getId(),
                            'chapterId' => $chapter->getSlug(),
        ]));
    }

    /**
     * Route affichant une page de confirmation de la suppression.
     *
     * @param $resourceId, $chapterId
     *
     * @return $lesson, $chapter, $form
     *
     * @Route(
     *      "confirm-delete/{resourceId}/{chapterId}",
     *      name="icap_lesson_confirm_delete_chapter",
     *      requirements={"resourceId" = "\d+", "chapterId" = "\d+"},
     *      options = {"expose"=true}
     * )
     * @ParamConverter("lesson", class="IcapLessonBundle:Lesson", options={"id" = "resourceId"})
     * @Template()
     */
    public function confirmDeleteChapterAction($lesson, $chapterId)
    {
        $this->checkAccess('EDIT', $lesson);
        $chapter = $this->findChapter($lesson, $chapterId);

        $chapterRepository = $this->getDoctrine()->getManager()->getRepository('IcapLessonBundle:Chapter');
        $childrenChapter = $chapterRepository->childCount($chapter);

        $form = $this->createForm(new DeleteChapterType(), $chapter, ['hasChildren' => $childrenChapter > 0]);
        $form->handleRequest($this->getRequest());

        //for ajaxification
        if ($this->getRequest()->isXMLHttpRequest()) {
            return $this->render(
                            'IcapLessonBundle:Lesson:deleteChapterPopup.html.twig', [
                        'lesson' => $lesson,
                        'chapter' => $chapter,
                        'form' => $form->createView(),
                        'haschild' => $childrenChapter,
                        'workspace' => $lesson->getResourceNode()->getWorkspace(),
                            ]
            );
        }

        return [
            'lesson' => $lesson,
            'chapter' => $chapter,
            'form' => $form->createView(),
            'haschild' => $childrenChapter,
            'workspace' => $lesson->getResourceNode()->getWorkspace(),
        ];
    }

    /**
     * Route effaçant le chapitre de la base.
     *
     * @param $resourceId, $chapter
     *
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
        $this->checkAccess('EDIT', $lesson);
        $translator = $this->get('translator');

        $chapter = $this->findChapter($lesson, $chapterId);

        $form = $this->createForm(new DeleteChapterType(), $chapter);
        $form->handleRequest($this->getRequest());

        //I wish I could do the form validation, but sometimes the form is always false with not errors
        //@todo add form validation
        $chaptername = $chapter->getTitle();
        $deleteChildren = false;

        if ($form->has('deletechildren')) {
            $deleteChildren = $form->get('deletechildren')->getData();
        }

        $em = $this->getDoctrine()->getManager();
        if ($deleteChildren) {
            $em->remove($chapter);
            $em->flush();
            $this->get('session')->getFlashBag()->add('success', $translator->trans('Your chapter has been deleted', [], 'icap_lesson'));
        } else {
            $repo = $em->getRepository('IcapLessonBundle:Chapter');
            $repo->removeFromTree($chapter);
            $em->flush();
            $this->get('session')->getFlashBag()->add('success', $translator->trans('Your chapter has been deleted but no subchapter', [], 'icap_lesson'));
        }
        $this->dispatchChapterDeleteEvent($lesson, $chaptername);

        return $this->redirect($this->generateUrl('icap_lesson', ['resourceId' => $lesson->getId()]));
    }

    /**
     * Return chapter creation form.
     *
     * @param $resourceId
     *
     * @return $lesson, $form
     *
     * @Route(
     *      "new/{resourceId}/",
     *      name="icap_lesson_new_chapter_without_parent",
     *      requirements={"resourceId" = "\d+"},
     *      defaults={"parentChapterId" = 0}
     * )
     *
     * @Route(
     *      "new/{resourceId}/{parentChapterId}",
     *      name="icap_lesson_new_chapter",
     *      requirements={"resourceId" = "\d+", "parentChapterId" = "\d+"},
     *      options = {"expose"=true}
     * )
     * @ParamConverter("lesson", class="IcapLessonBundle:Lesson", options={"id" = "resourceId"})
     * @Template()
     */
    public function newChapterAction($lesson, $parentChapterId)
    {
        $this->checkAccess('EDIT', $lesson);
        if ($parentChapterId === 0 || $parentChapterId === null) {
            $chapterParent = $lesson->getRoot();
        } else {
            $chapterParent = $this->findChapter($lesson, $parentChapterId);
        }

        $chapterRepository = $this->getDoctrine()->getManager()->getRepository('IcapLessonBundle:Chapter');
        $chapters = $chapterRepository->getChapterAndChapterChildren($lesson->getRoot());

        $form = $this->createForm($this->get('icap.lesson.chaptertype'), null, ['chapters' => $chapters, 'parentId' => $chapterParent->getId()]);

        //for ajaxification
        if ($this->getRequest()->isXMLHttpRequest()) {
            return $this->render(
                            'IcapLessonBundle:Lesson:newChapterAjaxified.html.twig', [
                        '_resource' => $lesson,
                        'form' => $form->createView(),
                        'chapterParent' => $chapterParent,
                        'workspace' => $lesson->getResourceNode()->getWorkspace(),
                            ]
            );
        }

        return [
            '_resource' => $lesson,
            'form' => $form->createView(),
            'workspace' => $lesson->getResourceNode()->getWorkspace(),
        ];
    }

    /**
     * Create a new chapter.
     *
     * @param $resourceId
     *
     * @return $lesson, $form
     *
     * @Route(
     *      "add/{resourceId}",
     *      name="icap_lesson_add_chapter",
     *      requirements={"resourceId" = "\d+"}
     * )
     * @Template("IcapLessonBundle:Lesson:newChapter.html.twig")
     * @ParamConverter("lesson", class="IcapLessonBundle:Lesson", options={"id" = "resourceId"})
     */
    public function addChapterAction($lesson)
    {
        $this->checkAccess('EDIT', $lesson);
        $translator = $this->get('translator');

        $chapterRepository = $this->getDoctrine()->getManager()->getRepository('IcapLessonBundle:Chapter');
        $chapters = $chapterRepository->getChapterAndChapterChildren($lesson->getRoot());

        $form = $this->createForm($this->get('icap.lesson.chaptertype'), null, ['chapters' => $chapters]);
        $form->handleRequest($this->getRequest());

        if ($form->isValid()) {
            $chapterParent = $this->findChapter($lesson, $form->get('parentChapter')->getData());
            $chapter = $form->getData();
            $chapter->setLesson($lesson);
            $em = $this->getDoctrine()->getManager();

            $chapterRepository->persistAsLastChildOf($chapter, $chapterParent);
            $em->flush();

            $this->dispatchChapterCreateEvent($lesson, $chapter);

            $this->get('session')->getFlashBag()->add('success', $translator->trans('Your chapter has been added', [], 'icap_lesson'));

            return $this->redirect($this->generateUrl('icap_lesson_chapter', ['resourceId' => $lesson->getId(), 'chapterId' => $chapter->getSlug()]));
        } else {
            $this->get('session')->getFlashBag()->add('error', $translator->trans('Your chapter has not been added', [], 'icap_lesson'));
        }

        return $this->redirect($this->generateUrl('icap_lesson_new_chapter', ['resourceId' => $lesson->getId(), 'parentChapterId' => $form->get('parentChapter')->getData()]));
    }

    /**
     * Create a new chapter (angular version).
     *
     * @Route(
     *      "api/add/{resourceId}",
     *      name="icap_lesson_add_ng_chapter",
     *      requirements={"resourceId" = "\d+"},
     *      options = {"expose"=true}
     * )
     * @Method("POST")
     * @ParamConverter("lesson", class="IcapLessonBundle:Lesson", options={"id" = "resourceId"})
     */
    public function addNgChapterAction($lesson, Request $request)
    {
        $this->checkAccess('EDIT', $lesson);

        $chapter = $request->request->get('chapter', false);
        $response = new JsonResponse();
        $response->setData($chapter);

        return $response;
    }

    /**
     * @Route(
     *      "choice-move/{resourceId}/{chapterId}",
     *      name="icap_lesson_choice_move_chapter",
     *      requirements={"resourceId" = "\d+", "chapterId" = "\d+"},
     *      options = {"expose"=true}
     * )
     * @Template()
     * @ParamConverter("lesson", class="IcapLessonBundle:Lesson", options={"id" = "resourceId"})
     */
    public function choiceMoveChapterAction($lesson, $chapterId, Request $request)
    {
        $this->checkAccess('EDIT', $lesson);
        $chapter = $this->findChapter($lesson, $chapterId);

        $form = $this->createForm($this->get('icap.lesson.movechaptertype'), $chapter, ['attr' => ['filter' => 1]]);
        $form->handleRequest($this->container->get('request_stack')->getCurrentRequest());

        //for ajaxification
        if ($request->isXMLHttpRequest()) {
            return $this->render(
                            'IcapLessonBundle:Lesson:choiceMoveChapterAjaxified.html.twig', [
                        '_resource' => $lesson,
                        'chapter' => $chapter,
                        'form' => $form->createView(),
                        'workspace' => $lesson->getResourceNode()->getWorkspace(),
                            ]
            );
        }

        return [
            '_resource' => $lesson,
            'chapter' => $chapter,
            'form' => $form->createView(),
            'workspace' => $lesson->getResourceNode()->getWorkspace(),
        ];
    }

    /**
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
        $this->checkAccess('EDIT', $lesson);

        $chapter = $this->findChapter($lesson, $chapterId);
        $oldparent = $chapter->getParent();
        $em = $this->getDoctrine()->getManager();
        $repo = $em->getRepository('IcapLessonBundle:Chapter');

        $form = $this->createForm($this->get('icap.lesson.movechaptertype'), $chapter, ['attr' => ['filter' => 0]]);
        $form->handleRequest($this->getRequest());
        if ($form->isValid() && $form->get('choiceChapter')->getData() !== $chapter->getid()) {
            $newParentId = $form->get('choiceChapter')->getData();
            $brother = $form->get('brother')->getData();
            $firstposition = $form->get('firstposition')->getData();
        } else {
            return [
                '_resource' => $lesson,
                'chapter' => $chapter,
                'form' => $form->createView(),
                'workspace' => $lesson->getResourceNode()->getWorkspace(),
            ];
        }

        $newParent = $this->findChapter($lesson, $newParentId);
        $path = $repo->getPath($newParent);
        foreach ($path as $currentParent) {
            if ($currentParent->getId() === $chapterId) {
                throw new \InvalidArgumentException();
            }
        }

        //a node cant be sibling with root
        if ($brother === true && $newParentId !== $lesson->getRoot()->getId()) {
            $repo->persistAsNextSiblingOf($chapter, $newParent);
        } else {
            if ($firstposition === 'true') {
                $repo->persistAsFirstChildOf($chapter, $newParent);
            } else {
                $repo->persistAsLastChildOf($chapter, $newParent);
            }
        }
        $em->flush();

        $this->dispatchChapterMoveEvent($lesson, $chapter, $oldparent, $newParent);

        return $this->redirect($this->generateUrl('icap_lesson_chapter', [
                            'resourceId' => $lesson->getId(),
                            'chapterId' => $chapter->getSlug(),
        ]));
    }

    /**
     * @Route(
     *      "duplicate_form/{resourceId}/{chapterId}",
     *      name="icap_lesson_duplicate_form_chapter",
     *      requirements={"resourceId" = "\d+", "chapterId" = "\d+"},
     *      options = {"expose"=true}
     * )
     * @Template("IcapLessonBundle:Lesson:duplicateChapter.html.twig")
     * @ParamConverter("lesson", class="IcapLessonBundle:Lesson", options={"id" = "resourceId"})
     */
    public function duplicateFormChapterAction($lesson, $chapterId)
    {
        $this->checkAccess('EDIT', $lesson);
        $chapter = $this->findChapter($lesson, $chapterId);

        $form = $this->createForm($this->get('icap.lesson.duplicatechaptertype'), $chapter);
        $form->handleRequest($this->getRequest());

        //for ajaxification
        if ($this->getRequest()->isXMLHttpRequest()) {
            return $this->render(
                            'IcapLessonBundle:Lesson:duplicateChapterAjaxified.html.twig', [
                        '_resource' => $lesson,
                        'chapter' => $chapter,
                        'form' => $form->createView(),
                        'workspace' => $lesson->getResourceNode()->getWorkspace(),
                            ]
            );
        }

        return [
            '_resource' => $lesson,
            'chapter' => $chapter,
            'form' => $form->createView(),
            'workspace' => $lesson->getResourceNode()->getWorkspace(),
        ];
    }

    /**
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
        $this->checkAccess('EDIT', $lesson);

        $chapter_manager = $this->container->get('icap.lesson.manager.chapter');
        $em = $this->getDoctrine()->getManager();

        $form = $this->createForm($this->get('icap.lesson.duplicatechaptertype'), $chapter);
        $form->handleRequest($this->getRequest());
        $parent = null;
        $copy_children = false;
        if ($form->isValid()) {
            $parent = $this->findChapter($lesson, $form->get('parent')->getData());
            if ($form->has('duplicate_children')) {
                $copy_children = $form->get('duplicate_children')->getData();
            }
        } else {
            return
                    $this->redirect(
                            $this->generateUrl(
                                    'icap_lesson_duplicate_form_chapter', [
                                'resourceId' => $lesson->getId(),
                                'chapterId' => $chapter->getId(),
                                    ]
                            )
            );
        }

        $chapter_copy = $chapter_manager->copyChapter($chapter, $parent, $copy_children, $form->get('title')->getData());
        $em->flush();

        $this->dispatchChapterCreateEvent($lesson, $chapter_copy);

        return $this->redirect($this->generateUrl('icap_lesson_chapter', [
                            'resourceId' => $lesson->getId(),
                            'chapterId' => $chapter_copy->getSlug(),
        ]));
    }

    /*
     * fonction recherchant un chapitre dans la base
     */

    private function findChapter($lesson, $chapterId)
    {
        $chapterRepository = $this->getDoctrine()->getManager()->getRepository('IcapLessonBundle:Chapter');
        $chapter = $chapterRepository->findOneBy(['id' => $chapterId, 'lesson' => $lesson]);
        if ($chapter === null) {
            throw new NotFoundHttpException();
        }

        return $chapter;
    }

    /**
     * @param Lesson  $lesson
     * @param Chapter $chapter
     *
     * @return Controller
     */
    protected function dispatchChapterReadEvent(Lesson $lesson, Chapter $chapter)
    {
        $event = new LogChapterReadEvent($lesson, $chapter);

        return $this->get('event_dispatcher')->dispatch('log', $event);
    }

    /**
     * @param Lesson  $lesson
     * @param Chapter $chapter
     * @param $changeSet
     *
     * @return Controller
     */
    protected function dispatchChapterUpdateEvent(Lesson $lesson, Chapter $chapter, $changeSet)
    {
        $event = new LogChapterUpdateEvent($lesson, $chapter, $changeSet);

        return $this->get('event_dispatcher')->dispatch('log', $event);
    }

    /**
     * @param Lesson  $lesson
     * @param Chapter $chapter
     *
     * @return Controller
     */
    protected function dispatchChapterCreateEvent(Lesson $lesson, Chapter $chapter)
    {
        $event = new LogChapterCreateEvent($lesson, $chapter);

        return $this->get('event_dispatcher')->dispatch('log', $event);
    }

    /**
     * @param Lesson  $lesson
     * @param Chapter $chapter
     *
     * @return Controller
     */
    protected function dispatchChapterDeleteEvent(Lesson $lesson, $chaptername)
    {
        $event = new LogChapterDeleteEvent($lesson, $chaptername);

        return $this->get('event_dispatcher')->dispatch('log', $event);
    }

    /**
     * @param Lesson  $lesson
     * @param Chapter $chapter
     *
     * @return Controller
     */
    protected function dispatchChapterMoveEvent(Lesson $lesson, Chapter $chapter, Chapter $oldchapter, Chapter $newchapter)
    {
        $event = new LogChapterMoveEvent($lesson, $chapter, $oldchapter, $newchapter);

        return $this->get('event_dispatcher')->dispatch('log', $event);
    }
}
