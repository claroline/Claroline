<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\ForumBundle\Controller;

use Claroline\ForumBundle\Entity\Message;
use Claroline\ForumBundle\Entity\Subject;
use Claroline\ForumBundle\Entity\Forum;
use Claroline\ForumBundle\Entity\Category;
use Claroline\ForumBundle\Form\MessageType;
use Claroline\ForumBundle\Form\SubjectType;
use Claroline\ForumBundle\Form\CategoryType;
use Claroline\ForumBundle\Form\EditTitleType;
use Claroline\ForumBundle\Form\ForumOptionsType;
use Claroline\ForumBundle\Event\Log\EditMessageEvent;
use Claroline\ForumBundle\Event\Log\EditSubjectEvent;
use Claroline\CoreBundle\Library\Resource\ResourceCollection;
use Claroline\CoreBundle\Entity\Workspace\AbstractWorkspace;
use Claroline\CoreBundle\Entity\User;
use Pagerfanta\Adapter\ArrayAdapter;
use Pagerfanta\Adapter\DoctrineORMAdapter;
use Pagerfanta\Pagerfanta;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\Form\FormError;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Response;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration as EXT;

/**
 * ForumController
 */
class ForumController extends Controller
{
    /**
     * @Route(
     *     "/{forum}/category",
     *     name="claro_forum_categories",
     *     defaults={"page"=1}
     * )
     * @EXT\ParamConverter("user", options={"authenticatedUser" = true})
     * @Template("ClarolineForumBundle::index.html.twig")
     *
     * @param integer $resourceId
     *
     * @return Response
     */
    public function openAction(Forum $forum, User $user)
    {
        $em = $this->getDoctrine()->getManager();
        $this->checkAccess($forum);
        $categories = $em->getRepository('ClarolineForumBundle:Forum')->findCategories($forum);
        $sc = $this->get('security.context');
        $isModerator = $sc->isGranted('moderate', new ResourceCollection(array($forum->getResourceNode())));

        return array(
            '_resource' => $forum,
            'isModerator' => $isModerator,
            'categories' => $categories
        );
    }

    /**
     * @Route(
     *     "/category/{category}/subjects/page/{page}",
     *     name="claro_forum_subjects",
     *     defaults={"page"=1}
     * )
     * @EXT\ParamConverter("user", options={"authenticatedUser" = true})
     * @Template("ClarolineForumBundle::subjects.html.twig")
     *
     * @param integer $resourceId
     *
     * @return Response
     */
    public function showSubjectsAction(Category $category, $page, User $user)
    {
        $em = $this->getDoctrine()->getManager();
        $forum = $category->getForum();
        $this->checkAccess($forum);
        $limits = $em->getRepository('ClarolineForumBundle:ForumOptions')->findAll();
        $limit = $limits[0]->getSubjects();
        $subjects = $em->getRepository('ClarolineForumBundle:Forum')->findSubjects($category);
        $adapter = new ArrayAdapter($subjects);
        $pager = new Pagerfanta($adapter);
        $pager->setMaxPerPage($limit);
        $pager->setCurrentPage($page);

        $collection = new ResourceCollection(array($forum->getResourceNode()));
        $sc = $this->get('security.context');
        $canCreateSubject = $sc->isGranted('post', $collection);
        $isModerator = $sc->isGranted('moderate', $collection);

        return array(
            'pager' => $pager,
            '_resource' => $forum,
            'canCreateSubject' => $canCreateSubject,
            'isModerator' => $isModerator,
            'hasSubscribed' => $this->get('claroline.manager.forum_manager')->hasSubscribed($user, $forum),
            'category' => $category
        );
    }

    /**
     * @Route(
     *     "/form/subject/{category}",
     *     name="claro_forum_form_subject_creation"
     * )
     *
     * @Template("ClarolineForumBundle::subjectForm.html.twig")
     *
     * @param integer $forumId
     *
     * @return Response
     */
    public function forumSubjectCreationFormAction(Category $category)
    {
        $em = $this->get('doctrine.orm.entity_manager');
        $forum = $category->getForum();
        $collection = new ResourceCollection(array($forum->getResourceNode()));

        if (!$this->get('security.context')->isGranted('post', $collection)) {
            throw new AccessDeniedHttpException($collection->getErrorsForDisplay());
        }

        $formSubject = $this->get('form.factory')->create(new SubjectType());

        return array(
            '_resource' => $forum,
            'form' => $formSubject->createView(),
            'category' => $category
        );
    }

    /**
     * @Route(
     *     "/form/category/{forum}",
     *     name="claro_forum_form_category_creation"
     * )
     *
     * @Template("ClarolineForumBundle::categoryForm.html.twig")
     *
     * @param Forum $forum
     *
     * @return Response
     */
    public function forumCategoryCreationFormAction(Forum $forum)
    {
        $collection = new ResourceCollection(array($forum->getResourceNode()));

        if (!$this->get('security.context')->isGranted('post', $collection)) {
            throw new AccessDeniedHttpException($collection->getErrorsForDisplay());
        }

        $formCategory = $this->get('form.factory')->create(new CategoryType());

        return array(
            '_resource' => $forum,
            'form' => $formCategory->createView()
        );
    }

    /**
     * @Route(
     *     "/category/create/{forum}",
     *     name="claro_forum_create_category"
     * )
     *
     * @param Forum $forum
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     *
     * @param \Claroline\ForumBundle\Entity\Forum $forum
     */
    public function createCategoryAction(Forum $forum)
    {
        $collection = new ResourceCollection(array($forum->getResourceNode()));

        if (!$this->get('security.context')->isGranted('post', $collection)) {
            throw new AccessDeniedHttpException($collection->getErrorsForDisplay());
        }

        $form = $this->get('form.factory')->create(new CategoryType(), new Category());
        $form->handleRequest($this->get('request'));

        if ($form->isValid()) {
            $category = $form->getData();
            $this->get('claroline.manager.forum_manager')->createCategory($forum, $category->getName());
        }

        return new RedirectResponse(
            $this->generateUrl('claro_forum_categories', array('forum' => $forum->getId()))
        );
    }

    /**
     *
     * The form submission is working but I had to do some weird things to make it works.
     * @Route(
     *     "/subject/create/{category}",
     *     name="claro_forum_create_subject"
     * )
     *
     * @Template("ClarolineForumBundle::subjectForm.html.twig")
     *
     * @param integer $forumId
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function createSubjectAction(Category $category)
    {
        $em = $this->getDoctrine()->getManager();
        $forum = $category->getForum();
        $collection = new ResourceCollection(array($forum->getResourceNode()));

        if (!$this->get('security.context')->isGranted('post', $collection)) {
            throw new AccessDeniedHttpException($collection->getErrorsForDisplay());
        }

        $form = $this->get('form.factory')->create(new SubjectType(), new Subject);
        $form->handleRequest($this->get('request'));

        if ($form->isValid()) {
            $user = $this->get('security.context')->getToken()->getUser();
            $subject = $form->getData();
            $subject->setCreator($user);
            //instantiation of the new resources
            $subject->setCategory($category);
            $this->get('claroline.manager.forum_manager')->createSubject($subject);
            $dataMessage = $form->get('message')->getData();

            if ($dataMessage['content'] !== null) {
                $message = new Message();
                $message->setContent($dataMessage['content']);
                $message->setCreator($user);
                $message->setSubject($subject);
                $this->get('claroline.manager.forum_manager')->createMessage($message);

                return new RedirectResponse(
                    $this->generateUrl('claro_forum_subjects', array('category' => $category->getId()))
                );
            }
        }

        throw new \Exception($form->getErrorsAsString());
        $form->get('message')->addError(
            new FormError($this->get('translator')->trans('field_content_required', array(), 'forum'))
        );

        return array(
            'form' => $form->createView(),
            '_resource' => $forum
        );
    }

    /**
     * @Route(
     *     "/subject/{subject}/messages/page/{page}",
     *     name="claro_forum_messages",
     *     defaults={"page"=1}
     * )
     *
     * @Template("ClarolineForumBundle::messages.html.twig")
     *
     * @return Response
     */
    public function showMessagesAction(Subject $subject, $page)
    {
        $em = $this->getDoctrine()->getManager();
        $forum = $subject->getCategory()->getForum();
        $this->checkAccess($forum);
        $limits = $em->getRepository('ClarolineForumBundle:ForumOptions')->findAll();
        $limit = $limits[0]->getMessages();
        $query = $em->getRepository('ClarolineForumBundle:Message')->findBySubject($subject, true);
        $adapter = new DoctrineORMAdapter($query);
        $pager = new Pagerfanta($adapter);
        $pager->setMaxPerPage($limit);
        $pager->setCurrentPage($page);
        $isModerator = $this->get('security.context')->isGranted('moderate', new ResourceCollection(array($forum->getResourceNode())));
        $collection = new ResourceCollection(array($forum->getResourceNode()));
        $canAnswer = $this->get('security.context')->isGranted('post', $collection);
        $form = $this->get('form.factory')->create(new MessageType());

        return array(
            'subject' => $subject,
            'pager' => $pager,
            '_resource' => $forum,
            'isModerator' => $isModerator,
            'form' => $form->createView(),
            'canAnswer' => $canAnswer,
            'category' => $subject->getCategory()
        );
    }

    /**
     * @Route(
     *     "/add/message/{subjectId}",
     *     name="claro_forum_message_form"
     * )
     *
     * @Template("ClarolineForumBundle::messageForm.html.twig")
     *
     * @param integer $subjectId
     *
     * @return Response
     */
    public function messageCreationFormAction($subjectId)
    {
        $em = $this->getDoctrine()->getManager();
        $subject = $em->getRepository('ClarolineForumBundle:Subject')->find($subjectId);
        $forum = $subject->getForum();
        $collection = new ResourceCollection(array($forum->getResourceNode()));

        if (!$this->get('security.context')->isGranted('post', $collection)) {
            throw new AccessDeniedHttpException($collection->getErrorsForDisplay());
        }

        $form = $this->get('form.factory')->create(new MessageType());

        return array(
            'subject' => $subject,
            'form' => $form->createView(),
            '_resource' => $forum
        );
    }

    /**
     * @Route(
     *     "/create/message/{subject}",
     *     name="claro_forum_create_message"
     * )
     *
     * @Template("ClarolineForumBundle::messageForm.html.twig")
     *
     * @param integer $subjectId
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function createMessageAction(Subject $subject)
    {
        $form = $this->container->get('form.factory')->create(new MessageType, new Message());
        $form->handleRequest($this->get('request'));
        $manager = $this->get('claroline.manager.forum_manager');
        $forum = $subject->getCategory()->getForum();
        $collection = new ResourceCollection(array($forum->getResourceNode()));

        if (!$this->get('security.context')->isGranted('post', $collection)) {
            throw new AccessDeniedHttpException($collection->getErrorsForDisplay());
        }

        if ($form->isValid()) {
            $message = $form->getData();
            $user = $this->get('security.context')->getToken()->getUser();
            $message->setCreator($user);
            $message->setSubject($subject);
            $manager->createMessage($message);

            return new RedirectResponse(
                $this->generateUrl('claro_forum_messages', array('subject' => $subject->getId()))
            );
        }

        return array(
            'subjectId' => $subject->getId(),
            'form' => $form->createView(),
            '_resource' => $forum
        );
    }

    /**
     * @Route(
     *     "/options/edit",
     *     name="claro_forum_edit_options"
     * )
     *
     * @Template("ClarolineForumBundle::pluginOptionsForm.html.twig")
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function editForumOptionsAction()
    {
        $em = $this->getDoctrine()->getManager();
        $forumOptions = $em->getRepository('ClarolineForumBundle:ForumOptions')->findAll();
        $form = $this->container->get('form.factory')->create(new ForumOptionsType(), $forumOptions[0]);
        $form->handleRequest($this->get('request'));

        if ($form->isValid()) {
            $forumOptions = $form->getData();
            $em->persist($forumOptions);
            $em->flush();

            return new RedirectResponse($this->generateUrl('claro_admin_plugins'));
        }

        return array('form' => $form->createView());
    }

    /**
     * @Route(
     *     "/edit/message/{message}/form",
     *     name="claro_forum_edit_message_form"
     * )
     *
     * @Template("ClarolineForumBundle::editMessageForm.html.twig")
     */
    public function editMessageFormAction(Message $message)
    {
        $sc = $this->get('security.context');
        $subject = $message->getSubject();
        $forum = $subject->getCategory()->getForum();
        $isModerator = $sc->isGranted('moderate', new ResourceCollection(array($forum->getResourceNode())));

        if (!$isModerator && $sc->getToken()->getUser() !== $message->getCreator()) {
            throw new AccessDeniedHttpException();
        }

        $form = $this->get('form.factory')->create(new MessageType(), $message);

        return array(
            'subject' => $subject,
            'form' => $form->createView(),
            'message' => $message,
            '_resource' => $forum
        );
    }

    /**
     * @Route(
     *     "/edit/message/{message}",
     *     name="claro_forum_edit_message"
     * )
     *
     * @Template("ClarolineForumBundle::editMessageForm.html.twig")
     */
    public function editMessageAction(Message $message)
    {
        $sc = $this->get('security.context');
        $subject = $message->getSubject();
        $forum = $subject->getCategory()->getForum();
        $isModerator = $sc->isGranted('moderate', new ResourceCollection(array($forum->getResourceNode())));

        if (!$isModerator && $sc->getToken()->getUser() !== $message->getCreator()) {
            throw new AccessDeniedHttpException();
        }

        $form = $this->container->get('form.factory')->create(new MessageType, new Message());
        $form->handleRequest($this->get('request'));
        $em = $this->getDoctrine()->getManager();

        if ($form->isValid()) {
            $this->dispatch(new EditMessageEvent($message, $message->getContent(), $form->get('content')->getData()));
            $message->setContent($form->get('content')->getData());
            $em->persist($message);
            $em->flush();

            return new RedirectResponse(
                $this->generateUrl('claro_forum_messages', array('subject' => $subject->getId()))
            );
        }

        return array(
            'subject' => $subject,
            'form' => $form->createView(),
            'message' => $message,
            '_resource' => $forum
        );
    }

    /**
     * @Route(
     *     "/edit/category/{category}/form",
     *     name="claro_forum_edit_category_form"
     * )
     *
     * @Template("ClarolineForumBundle::editCategoryForm.html.twig")
     */
    public function editCategoryFormAction(Category $category)
    {
        $sc = $this->get('security.context');
        $forum = $category->getForum();
        $isModerator = $sc->isGranted('moderate', new ResourceCollection(array($forum->getResourceNode())));

        if (!$isModerator && $sc->getToken()->getUser()) {
            throw new AccessDeniedHttpException();
        }

        $form = $this->container->get('form.factory')->create(new CategoryType, $category);
        $form->handleRequest($this->get('request'));

        return array(
            'category' => $category,
            'form' => $form->createView(),
            '_resource' => $category->getForum()
        );
    }

    /**
     * @Route(
     *     "/edit/category/{category}",
     *     name="claro_forum_edit_category"
     * )
     */
    public function editCategoryAction(Category $category)
    {
        $sc = $this->get('security.context');
        $forum = $category->getForum();
        $isModerator = $sc->isGranted('moderate', new ResourceCollection(array($forum->getResourceNode())));

        if (!$isModerator && $sc->getToken()->getUser()) {
            throw new AccessDeniedHttpException();
        }

        $form = $this->container->get('form.factory')->create(new CategoryType, $category);
        $form->handleRequest($this->get('request'));

        if ($form->isValid()) {
            $em = $this->get('doctrine.orm.entity_manager');
            $em->persist($category);
            $em->flush();

            return new RedirectResponse(
                $this->generateUrl('claro_forum_categories', array('forum' => $category->getForum()->getId()))
            );
        }
    }

    /**
     * @Route(
     *     "/delete/category/{category}",
     *     name="claro_forum_delete_category"
     * )
     *
     * @param \Claroline\ForumBundle\Entity\Subject $subject
     */
    public function deleteCategory(Category $category)
    {
        $sc = $this->get('security.context');

        if ($sc->isGranted('moderate', new ResourceCollection(array($category->getForum()->getResourceNode())))) {

            $this->get('claroline.manager.forum_manager')->deleteCategory($category);

            return new RedirectResponse(
                $this->generateUrl('claro_forum_subjects', array('category' => $category->getId()))
            );
        }

        throw new AccessDeniedHttpException();
    }

    /**
     * @Route(
     *     "/{forum}/search/{search}/page/{page}",
     *     name="claro_forum_search",
     *     defaults={"page"=1, "search"= ""},
     *     options={"expose"=true}
     * )
     * @Template("ClarolineForumBundle::searchResults.html.twig")
     */
    public function searchAction(Forum $forum, $page, $search)
    {
        $em = $this->getDoctrine()->getManager();
        $repo = $em->getRepository('ClarolineForumBundle:Forum');
        $query = $repo->search($forum, $search);
        $adapter = new DoctrineORMAdapter($query);
        $pager = new Pagerfanta($adapter);
        $pager->setMaxPerPage(20);
        $pager->setCurrentPage($page);

        return array('pager' => $pager, '_resource' => $forum, 'search' => $search, 'page' => $page);
    }

     /**
     * @Route(
     *     "/edit/subject/{subjectId}/form",
     *     name="claro_forum_edit_subject_form"
     * )
     * @ParamConverter(
     *      "subject",
     *      class="ClarolineForumBundle:Subject",
     *      options={"id" = "subjectId", "strictId" = true}
     * )
     * @Template("ClarolineForumBundle::editSubjectForm.html.twig")
     *
     */
    public function editSubjectFormAction(Subject $subject)
    {
        $sc = $this->get('security.context');
        $isModerator = $sc->isGranted('moderate', new ResourceCollection(array($subject->getCategory()->getForum()->getResourceNode())));

        if (!$isModerator && $sc->getToken()->getUser() !== $subject->getCreator()) {
            throw new AccessDeniedHttpException();
        }

        $form = $this->container->get('form.factory')->create(new EditTitleType(), $subject);

        return array(
            'form' => $form->createView(),
            'subject' => $subject,
            'forumId' => $subject->getCategory()->getForum()->getId(),
            '_resource' => $subject->getCategory()->getForum()
        );
    }

    /**
     * @Route(
     *     "/edit/subject/{subjectId}/submit",
     *     name="claro_forum_edit_subject"
     * )
     * @ParamConverter(
     *      "subject",
     *      class="ClarolineForumBundle:Subject",
     *      options={"id" = "subjectId", "strictId" = true}
     * )
     * @Template("ClarolineForumBundle::editSubjectForm.html.twig")
     */
    public function editSubjectAction(Subject $subject)
    {
        $sc = $this->get('security.context');
        $isModerator = $sc->isGranted(
            'moderate', new ResourceCollection(array($subject->getCategory()->getForum()->getResourceNode()))
        );
        $em = $this->getDoctrine()->getManager();

        if (!$isModerator && $sc->getToken()->getUser() !== $subject->getCreator()) {
            throw new AccessDeniedHttpException();
        }

        $oldTitle = $subject->getTitle();
        $form = $this->container->get('form.factory')->create(new EditTitleType(), $subject);
        $form->handleRequest($this->get('request'));

        if ($form->isValid()) {
            $em->persist($subject);
            $em->flush();
            $this->dispatch(new EditSubjectEvent($subject, $oldTitle, $subject->getTitle()));

            return new RedirectResponse(
                $this->generateUrl('claro_forum_subjects', array('category' => $subject->getCategory()->getId()))
            );
        }

        return array(
            'form' => $form->createView(),
            'subjectId' => $subject->getId(),
            'forumId' => $subject->getForum()->getId(),
            '_resource' => $subject->getForum()
        );
    }

    /**
     * @Route(
     *     "/delete/message/{message}",
     *     name="claro_forum_delete_message"
     * )
     *
     * @param \Claroline\ForumBundle\Entity\Message $message
     */
    public function deleteMessageAction(Message $message)
    {
        $sc = $this->get('security.context');

        if ($sc->isGranted('moderate', new ResourceCollection(array($message->getSubject()->getCategory()->getForum()->getResourceNode())))) {
            $this->get('claroline.manager.forum_manager')->deleteMessage($message);

            return new RedirectResponse(
                $this->generateUrl('claro_forum_messages', array('subject' => $message->getSubject()->getId()))
            );
        }

        throw new AccessDeniedHttpException();
    }

    /**
     * @Route(
     *     "/subscribe/forum/{forum}",
     *     name="claro_forum_subscribe"
     * )
     * @EXT\ParamConverter("user", options={"authenticatedUser" = true})
     *
     * @param \Claroline\ForumBundle\Entity\Forum $forum
     */
    public function subscribeAction(Forum $forum, User $user)
    {
        $manager = $this->get('claroline.manager.forum_manager');
        $manager->subscribe($forum, $user);

        return new RedirectResponse(
            $this->generateUrl('claro_forum_subjects', array('forumId' => $forum->getId()))
        );
    }

    /**
     * @Route(
     *     "/unsubscribe/forum/{forum}",
     *     name="claro_forum_unsubscribe"
     * )
     * @EXT\ParamConverter("user", options={"authenticatedUser" = true})
     *
     * @param \Claroline\ForumBundle\Entity\Forum $forum
     */
    public function unsubscribeAction(Forum $forum, User $user)
    {
        $manager = $this->get('claroline.manager.forum_manager');
        $manager->unsubscribe($forum, $user);

        return new RedirectResponse(
            $this->generateUrl('claro_forum_subjects', array('forumId' => $forum->getId()))
        );
    }

    /**
     * @Route(
     *     "/delete/subject/{subject}",
     *     name="claro_forum_delete_subject"
     * )
     *
     * @param \Claroline\ForumBundle\Entity\Subject $subject
     */
    public function deleteSubjectAction(Subject $subject)
    {
        $sc = $this->get('security.context');

        if ($sc->isGranted('moderate', new ResourceCollection(array($subject->getCategory()->getForum()->getResourceNode())))) {

            $this->get('claroline.manager.forum_manager')->deleteSubject($subject);

            return new RedirectResponse(
                $this->generateUrl('claro_forum_subjects', array('category' => $subject->getCategory()->getId()))
            );
        }

        throw new AccessDeniedHttpException();
    }

    private function checkAccess(Forum $forum)
    {
        $collection = new ResourceCollection(array($forum->getResourceNode()));

        if (!$this->get('security.context')->isGranted('OPEN', $collection)) {
            throw new AccessDeniedHttpException($collection->getErrorsForDisplay());
        }
    }

    protected function dispatch($event)
    {
        $this->get('event_dispatcher')->dispatch('log', $event);

        return $this;
    }

    /**
     * @EXT\Route(
     *     "/forums/workspace/{workspaceId}",
     *     name="claro_workspace_forums",
     *     options={"expose"=true}
     * )
     * @EXT\Method("GET")
     * @EXT\ParamConverter(
     *      "workspace",
     *      class="ClarolineCoreBundle:Workspace\AbstractWorkspace",
     *      options={"id" = "workspaceId", "strictId" = true}
     * )
     *
     * @EXT\Template()
     *
     * Renders last messages from the forums' workspace
     *
     * @return Response
     */
    public function forumsWorkspaceWidgetAction(AbstractWorkspace $workspace)
    {
        $sc = $this->get('security.context');
        $user = $sc->getToken()->getUser();
        $utils = $this->get('claroline.security.utilities');
        $token = $sc->getToken($user);
        $roles = $utils->getRoles($token);

        $workspaces = array();
        $workspaces[] = $workspace;
        $em = $this->getDoctrine()->getManager();
        // Get the 3 last messages from all forums from the workspace
        $messages = $em->getRepository('ClarolineForumBundle:Message')
                ->findNLastByForum($workspaces, $roles,3);

        return array('widgetType' => 'workspace', 'messages' => $messages);
    }

    /**
     * @EXT\Route(
     *     "/forums",
     *     name="claro_desktop_forums",
     *     options={"expose"=true}
     * )
     * @EXT\Method("GET")
     *
     * @EXT\Template()
     *
     * Renders last messages from the forums' workspaces
     *
     * @return Response
     */
    public function forumsDesktopWidgetAction()
    {
        $sc = $this->get('security.context');
        $user = $sc->getToken()->getUser();
        $utils = $this->get('claroline.security.utilities');
        $token = $sc->getToken();
        $roles = $utils->getRoles($token);

        // Get user workspaces  
        $manager = $this->get('claroline.manager.workspace_manager');
        $workspaces = $manager->getWorkspacesByUser($user);
        $em = $this->getDoctrine()->getManager();

        // Get the 3 last messages from all forums from the workspaces
        $messages = $em->getRepository('ClarolineForumBundle:Message')
                ->findNLastByForum($workspaces, $roles,3);

        return array('widgetType' => 'desktop', 'messages' => $messages);
    }
}
