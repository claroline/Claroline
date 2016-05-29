<?php

namespace Icap\LessonBundle\Controller\API;

use FOS\RestBundle\Controller\FOSRestController;
use FOS\RestBundle\Controller\Annotations\NamePrefix;
use FOS\RestBundle\Controller\Annotations\View;
use FOS\RestBundle\Controller\Annotations\Get;
use FOS\RestBundle\Controller\Annotations\Post;
use FOS\RestBundle\Controller\Annotations\Patch;
use FOS\RestBundle\Controller\Annotations\Put;
use FOS\RestBundle\Controller\Annotations\Delete;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Icap\LessonBundle\Entity\Chapter;
use Icap\LessonBundle\Entity\Lesson;
use Icap\LessonBundle\Form\ChapterType;
use Claroline\CoreBundle\Manager\LocaleManager;
use JMS\DiExtraBundle\Annotation as DI;
use Claroline\CoreBundle\Library\Resource\ResourceCollection;
use Claroline\CoreBundle\Event\Log\LogResourceReadEvent;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Form\FormFactory;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Icap\LessonBundle\Event\Log\LogChapterReadEvent;
use Icap\LessonBundle\Event\Log\LogChapterUpdateEvent;
use Icap\LessonBundle\Event\Log\LogChapterCreateEvent;
use Icap\LessonBundle\Event\Log\LogChapterDeleteEvent;
use Icap\LessonBundle\Event\Log\LogChapterMoveEvent;

/**
 * @NamePrefix("icap_lesson_api_")
 */
class ApiController extends FOSRestController
{
    /**
     * @DI\InjectParams({
     *     "localeManager" = @DI\Inject("claroline.manager.locale_manager"),
     *     "formFactory"   = @DI\Inject("form.factory"),
     *     "request"       = @DI\Inject("request")
     * })
     */
    public function __construct(LocaleManager $localeManager, FormFactory $formFactory, Request $request)
    {
        $this->localeManager = $localeManager;
        $this->formFactory = $formFactory;
        $this->request = $request;
    }

    /**
     * @Get("/defaultChapter/{lesson}",
     *     requirements={"lesson" = "\d+"})
     * @ParamConverter("lesson", class="IcapLessonBundle:Lesson")
     */
    public function getDefaultChapterAction(Lesson $lesson)
    {
        // CHECK ACCESS
        $this->apiCheckAccess('OPEN', $lesson);

        $repo = $this->getDoctrine()->getManager()->getRepository('IcapLessonBundle:Chapter');
        $defaultChapter = $repo->getFirstChapter($lesson);

        $slug = $defaultChapter
            ? $defaultChapter->getSlug()
            : null;

        return array(
            'defaultChapter' => $slug,
        );
    }

    /**
     * @Get("/tree/{lesson}",
     *      requirements={"lesson" = "\d+"})
     * @ParamConverter("lesson", class="IcapLessonBundle:Lesson")
     */
    public function getTreeAction(Lesson $lesson)
    {
        // CHECK ACCESS
        $this->apiCheckAccess('OPEN', $lesson);

        // Get chapter tree for this lesson but without chapter text
        $repo = $this->getDoctrine()->getManager()->getRepository('IcapLessonBundle:Chapter');
        $tmp_tree = $repo->buildChapterTree($lesson->getRoot(), 'chapter.id, chapter.level, chapter.title, chapter.slug');
        $tree = $tmp_tree[0];

        return $tree;
    }

    /**
     * @Get("/chapterlist/{lesson}",
     *      requirements={"lesson" = "\d+"})
     * @ParamConverter("lesson", class="IcapLessonBundle:Lesson")
     */
    public function getChapterListAction(Lesson $lesson)
    {
        // CHECK ACCESS
        $this->apiCheckAccess('OPEN', $lesson);

        $repo = $this->getDoctrine()->getManager()->getRepository('IcapLessonBundle:Chapter');

        return $repo->getChapterAndChapterChildren($lesson->getRoot());
    }

    /**
     * @Get("/chapters/{lesson}/{chapter}",
     *      requirements={"lesson" = "\d+"})
     * @ParamConverter("lesson", class="IcapLessonBundle:Lesson")
     * @ParamConverter("chapter", class="IcapLessonBundle:Chapter", options={"mapping": {"chapter": "slug"}})
     */
    public function viewChapterAction(Lesson $lesson, Chapter $chapter)
    {
        // CHECK ACCESS
        $this->apiCheckAccess('OPEN', $lesson);

        // Simulate a 401 error
        /*$response = new JsonResponse();
        $response->setStatusCode(Response::HTTP_UNAUTHORIZED);
        return $response;*/

        $this->dispatchChapterReadEvent($lesson, $chapter);

        return $this->formatChapterData($chapter);
    }

    /**
     * @Post("/chapters/{lesson}",
     *      requirements={"lesson" = "\d+"})
     * @ParamConverter("lesson", class="IcapLessonBundle:Lesson")
     * @View(serializerEnableMaxDepthChecks=true)
     */
    public function createChapterAction(Lesson $lesson)
    {
        // CHECK ACCESS
        $this->apiCheckAccess('EDIT', $lesson);

        $translator = $this->get('translator');

        $chapterType = new ChapterType($this->localeManager);
        $defaults = array(
            'csrf_protection' => false,
            'allow_extra_fields' => true,
        );
        $form = $this->formFactory->create($chapterType, new Chapter(), $defaults);
        $payload = $this->request->request->get('chapter');

        // Lesson root is the default parent
        if (!isset($payload['parent'])) {
            $payload['parent'] = $lesson->getRoot()->getSlug();
        }

        $form->submit($payload);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $repo = $em->getRepository('IcapLessonBundle:Chapter');

            $chapterParent = $repo->findOneBy(array(
                'lesson' => $lesson,
                'slug' => $payload['parent'],
            ));

            $chapter = $form->getData();
            $chapter->setLesson($lesson);

            $repo->persistAsLastChildOf($chapter, $chapterParent);
            $em->flush();

            $this->dispatchChapterCreateEvent($lesson, $chapter);

            return array(
                'message' => $translator->trans('Your chapter has been added', array(), 'icap_lesson'),
                'chapter' => $this->formatChapterData($chapter),
            );
        }

        // Form is not valid. What should we return?
        return array(
            'message' => $translator->trans('Your chapter has not been added', array(), 'icap_lesson'),
            'errors' => $form,
        );
    }

    /**
     * @Post("/chapters/{lesson}/{chapter}/duplicate",
     *      requirements={"lesson" = "\d+"})
     * @ParamConverter("lesson", class="IcapLessonBundle:Lesson")
     * @ParamConverter("chapter", class="IcapLessonBundle:Chapter", options={"mapping": {"chapter": "slug"}})
     */
    public function duplicateChapterAction(Lesson $lesson, Chapter $chapter)
    {
        // CHECK ACCESS
        $this->apiCheckAccess('EDIT', $lesson);

        $translator = $this->get('translator');

        $em = $this->getDoctrine()->getManager();
        $repo = $em->getRepository('IcapLessonBundle:Chapter');

        $defaults = array(
            'csrf_protection' => false,
            'allow_extra_fields' => true,
        );
        $form = $this->createForm($this->get('icap.lesson.duplicatechaptertype'), $chapter, $defaults);

        $payload = $this->request->request->get('chapter');

        // The form expects the id of the parent, not its slug
        $parent = $repo->findOneBy(array(
            'lesson' => $lesson,
            'slug' => $payload['parent'],
        ));
        $payload['parent'] = $parent->getId();

        $form->submit($payload);

        if ($form->isValid()) {
            $chapter_manager = $this->container->get('icap.lesson.manager.chapter');

            $chapter_copy = $chapter_manager->copyChapter(
                    $chapter, $parent, $payload['copyChildren'], $payload['title']
            );

            $this->dispatchChapterCreateEvent($lesson, $chapter_copy);

            return array(
                'message' => $translator->trans('Your chapter has been added', array(), 'icap_lesson'),
                'chapter' => $this->formatChapterData($chapter_copy),
            );
        }
        // Form is not valid. What should we return?
        return array(
            'message' => $translator->trans('Your chapter has not been added', array(), 'icap_lesson'),
            'errors' => $form,
        );
    }

    /**
     * @Put("/chapters/{lesson}/{chapter}",
     *      requirements={"lesson" = "\d+"})
     * @ParamConverter("lesson", class="IcapLessonBundle:Lesson")
     * @ParamConverter("chapter", class="IcapLessonBundle:Chapter", options={"mapping": {"chapter": "slug"}})
     */
    public function editChapterAction(Lesson $lesson, Chapter $chapter)
    {
        // CHECK ACCESS
        $this->apiCheckAccess('EDIT', $lesson);

        $translator = $this->get('translator');

        $chapterType = new ChapterType($this->localeManager);
        $defaults = array(
            'csrf_protection' => false,
            'allow_extra_fields' => true,
        );
        $form = $this->formFactory->create($chapterType, new Chapter(), $defaults);
        $payload = $this->request->request->get('chapter');
        $form->submit($payload);

        if ($form->isValid()) {
            $chapter->setTitle($payload['title']);

            // Chapter text is not mandatory
            if (array_key_exists('text', $payload)) {
                $chapter->setText($payload['text']);
            }

            // Set slug to null in order to re-generate it
            $chapter->setSlug(null);

            $em = $this->getDoctrine()->getManager();
            $unitOfWork = $em->getUnitOfWork();
            $unitOfWork->computeChangeSets();
            $changeSet = $unitOfWork->getEntityChangeSet($chapter);
            $em->flush();

            $this->dispatchChapterUpdateEvent($lesson, $chapter, $changeSet);

            return array(
                'message' => $translator->trans('Your chapter has been modified', array(), 'icap_lesson'),
                'chapter' => $this->formatChapterData($chapter),
            );
        }

        // Form is not valid. What should we return?
        return array(
            'message' => $translator->trans('Your chapter has not been modified', array(), 'icap_lesson'),
            'errors' => $form,
        );
    }

    /**
     * @Patch("/chapters/{lesson}/{chapter}",
     *          requirements={"lesson" = "\d+"})
     * @ParamConverter("lesson", class="IcapLessonBundle:Lesson")
     * @ParamConverter("chapter", class="IcapLessonBundle:Chapter", options={"mapping": {"chapter": "slug"}})
     * @View(serializerEnableMaxDepthChecks=true)
     */
    public function moveChapterAction(Lesson $lesson, Chapter $chapter)
    {
        // CHECK ACCESS
        $this->apiCheckAccess('EDIT', $lesson);

        $translator = $this->get('translator');

        $oldParent = $chapter->getParent();
        $newParentSlug = $this->request->request->get('newParent');
        $prevSiblingSlug = $this->request->request->get('prevSibling');

        $em = $this->getDoctrine()->getManager();
        $repo = $em->getRepository('IcapLessonBundle:Chapter');
        $newParent = $repo->findOneBySlug($newParentSlug);

        if ($prevSiblingSlug == null) {
            $repo->persistAsFirstChildOf($chapter, $newParent);
        } else {
            $prevSibling = $repo->findOneBySlug($prevSiblingSlug);
            $repo->persistAsNextSiblingOf($chapter, $prevSibling);
        }

        $em->flush();

        $this->dispatchChapterMoveEvent($lesson, $chapter, $oldParent, $chapter->getParent());

        return array(
            'message' => $translator->trans('Your chapter has been modified', array(), 'icap_lesson'),
            'chapter' => $this->formatChapterData($chapter),
        );
    }

    /**
     * @Delete("/chapters/{lesson}/{chapter}",
     *      requirements={"lesson" = "\d+"})
     * @ParamConverter("lesson", class="IcapLessonBundle:Lesson")
     * @ParamConverter("chapter", class="IcapLessonBundle:Chapter", options={"mapping": {"chapter": "slug"}})
     */
    public function deleteChapterAction(Lesson $lesson, Chapter $chapter)
    {
        // CHECK ACCESS
        $this->apiCheckAccess('EDIT', $lesson);

        $translator = $this->get('translator');
        $message = null;

        $chapterTitle = $chapter->getTitle();

        // DELETE request doesn't allow to send body params, so we have to rely on a query param instead
        $deleteChildren = $this->request->query->get('deleteChildren');

        $em = $this->getDoctrine()->getManager();
        $repo = $em->getRepository('IcapLessonBundle:Chapter');

        if ($deleteChildren == 'true') {
            $em->remove($chapter);
            $message = $translator->trans('Your chapter has been deleted', array(), 'icap_lesson');
        } else {
            $repo->removeFromTree($chapter);
            $message = $translator->trans('Your chapter has been deleted but no subchapter', array(), 'icap_lesson');
        }

        $em->flush();

        $this->dispatchChapterDeleteEvent($lesson, $chapterTitle);

        return array(
            'message' => $message,
        );
    }

    /**
     * @param string $permission
     * @param Lesson $lesson
     *
     * @throws AccessDeniedException
     */
    protected function apiCheckAccess($permission, Lesson $lesson)
    {
        $translator = $this->get('translator');

        $collection = new ResourceCollection(array($lesson->getResourceNode()));
        if (!$this->get('security.authorization_checker')->isGranted($permission, $collection)) {
            throw new HttpException(401, $translator->trans('error_401', array(), 'icap_lesson'));
        }

        $logEvent = new LogResourceReadEvent($lesson->getResourceNode());
        $this->get('event_dispatcher')->dispatch('log', $logEvent);
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

    private function formatChapterData(Chapter $chapter)
    {
        $repo = $this->getDoctrine()->getManager()->getRepository('IcapLessonBundle:Chapter');

        $next = $repo->getNextChapter($chapter);
        $nextSlug = !is_null($next) ? $next->getSlug() : null;

        $previous = $repo->getPreviousChapter($chapter);
        $previousSlug = !is_null($previous) ? $previous->getSlug() : null;

        $children = count($repo->getChildren($chapter));

        return array(
            'id' => $chapter->getId(),
            'slug' => $chapter->getSlug(),
            'title' => $chapter->getTitle(),
            'text' => $chapter->getText(),
            'lesson' => $chapter->getLesson()->getId(),
            'previous' => $previousSlug,
            'next' => $nextSlug,
            'parent' => $chapter->getParent()->getSlug(),
            'hasChildren' => $children > 0 ? true : false,
        );
    }
}
