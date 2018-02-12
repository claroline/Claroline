<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\ForumBundle\Manager;

use Claroline\CoreBundle\Entity\Resource\AbstractResourceEvaluation;
use Claroline\CoreBundle\Entity\Resource\ResourceNode;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Entity\Widget\WidgetInstance;
use Claroline\CoreBundle\Library\Security\Collection\ResourceCollection;
use Claroline\CoreBundle\Library\Security\Utilities;
use Claroline\CoreBundle\Manager\MailManager;
use Claroline\CoreBundle\Manager\MaskManager;
use Claroline\CoreBundle\Manager\Resource\ResourceEvaluationManager;
use Claroline\CoreBundle\Manager\ResourceManager;
use Claroline\CoreBundle\Manager\RightsManager;
use Claroline\CoreBundle\Manager\WorkspaceManager;
use Claroline\CoreBundle\Pager\PagerFactory;
use Claroline\CoreBundle\Persistence\ObjectManager;
use Claroline\ForumBundle\Entity\Category;
use Claroline\ForumBundle\Entity\Forum;
use Claroline\ForumBundle\Entity\Message;
use Claroline\ForumBundle\Entity\Notification;
use Claroline\ForumBundle\Entity\Subject;
use Claroline\ForumBundle\Entity\Widget\LastMessageWidgetConfig;
use Claroline\ForumBundle\Event\Log\CloseSubjectEvent;
use Claroline\ForumBundle\Event\Log\CreateCategoryEvent;
use Claroline\ForumBundle\Event\Log\CreateMessageEvent;
use Claroline\ForumBundle\Event\Log\CreateSubjectEvent;
use Claroline\ForumBundle\Event\Log\DeleteCategoryEvent;
use Claroline\ForumBundle\Event\Log\DeleteMessageEvent;
use Claroline\ForumBundle\Event\Log\DeleteSubjectEvent;
use Claroline\ForumBundle\Event\Log\EditCategoryEvent;
use Claroline\ForumBundle\Event\Log\EditMessageEvent;
use Claroline\ForumBundle\Event\Log\EditSubjectEvent;
use Claroline\ForumBundle\Event\Log\MoveMessageEvent;
use Claroline\ForumBundle\Event\Log\MoveSubjectEvent;
use Claroline\ForumBundle\Event\Log\OpenSubjectEvent;
use Claroline\ForumBundle\Event\Log\StickSubjectEvent;
use Claroline\ForumBundle\Event\Log\SubscribeForumEvent;
use Claroline\ForumBundle\Event\Log\UnstickSubjectEvent;
use Claroline\ForumBundle\Event\Log\UnsubscribeForumEvent;
use Claroline\MessageBundle\Manager\MessageManager;
use JMS\DiExtraBundle\Annotation as DI;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Translation\TranslatorInterface;

/**
 * @DI\Service("claroline.manager.forum_manager")
 */
class Manager
{
    private $authorization;
    private $container;
    private $dispatcher;
    private $mailManager;
    private $maskManager;
    private $messageManager;
    private $om;
    private $pagerFactory;
    private $resourceManager;
    private $rightsManager;
    private $router;
    private $securityUtilities;
    private $tokenStorage;
    private $translator;
    private $workspaceManager;
    private $resourceEvalManager;

    private $forumRepo;
    private $lastMessageWidgetConfigRepo;
    private $messageRepo;
    private $notificationRepo;
    private $roleRepo;
    private $subjectRepo;
    private $userRepo;

    /**
     * Constructor.
     *
     * @DI\InjectParams({
     *     "authorization"       = @DI\Inject("security.authorization_checker"),
     *     "container"           = @DI\Inject("service_container"),
     *     "dispatcher"          = @DI\Inject("event_dispatcher"),
     *     "mailManager"         = @DI\Inject("claroline.manager.mail_manager"),
     *     "maskManager"         = @DI\Inject("claroline.manager.mask_manager"),
     *     "messageManager"      = @DI\Inject("claroline.manager.message_manager"),
     *     "om"                  = @DI\Inject("claroline.persistence.object_manager"),
     *     "pagerFactory"        = @DI\Inject("claroline.pager.pager_factory"),
     *     "resourceManager"     = @DI\Inject("claroline.manager.resource_manager"),
     *     "rightsManager"       = @DI\Inject("claroline.manager.rights_manager"),
     *     "router"              = @DI\Inject("router"),
     *     "securityUtilities"   = @DI\Inject("claroline.security.utilities"),
     *     "tokenStorage"        = @DI\Inject("security.token_storage"),
     *     "translator"          = @DI\Inject("translator"),
     *     "workspaceManager"    = @DI\Inject("claroline.manager.workspace_manager"),
     *     "resourceEvalManager" = @DI\Inject("claroline.manager.resource_evaluation_manager")
     * })
     */
    public function __construct(
        AuthorizationCheckerInterface $authorization,
        ContainerInterface $container,
        EventDispatcherInterface $dispatcher,
        MailManager $mailManager,
        MaskManager $maskManager,
        MessageManager $messageManager,
        ObjectManager $om,
        PagerFactory $pagerFactory,
        ResourceManager $resourceManager,
        RightsManager $rightsManager,
        RouterInterface $router,
        Utilities $securityUtilities,
        TokenStorageInterface $tokenStorage,
        TranslatorInterface $translator,
        WorkspaceManager $workspaceManager,
        ResourceEvaluationManager $resourceEvalManager
    ) {
        $this->authorization = $authorization;
        $this->container = $container;
        $this->dispatcher = $dispatcher;
        $this->mailManager = $mailManager;
        $this->maskManager = $maskManager;
        $this->messageManager = $messageManager;
        $this->om = $om;
        $this->pagerFactory = $pagerFactory;
        $this->resourceManager = $resourceManager;
        $this->rightsManager = $rightsManager;
        $this->router = $router;
        $this->securityUtilities = $securityUtilities;
        $this->tokenStorage = $tokenStorage;
        $this->translator = $translator;
        $this->workspaceManager = $workspaceManager;
        $this->resourceEvalManager = $resourceEvalManager;
        $this->forumRepo = $om->getRepository('ClarolineForumBundle:Forum');
        $this->lastMessageWidgetConfigRepo = $om->getRepository('ClarolineForumBundle:Widget\LastMessageWidgetConfig');
        $this->messageRepo = $om->getRepository('ClarolineForumBundle:Message');
        $this->notificationRepo = $om->getRepository('ClarolineForumBundle:Notification');
        $this->roleRepo = $om->getRepository('ClarolineCoreBundle:Role');
        $this->subjectRepo = $om->getRepository('ClarolineForumBundle:Subject');
        $this->userRepo = $om->getRepository('ClarolineCoreBundle:User');
        $this->resourceEvalManager = $resourceEvalManager;
    }

    /**
     * Subscribe a user to a forum. A email will be sent to the user each time
     * a message is posted.
     *
     * @param \Claroline\ForumBundle\Entity\Forum $forum
     * @param \Claroline\CoreBundle\Entity\User   $user
     */
    public function subscribe(Forum $forum, User $user, $selfActivation = true)
    {
        $this->om->startFlushSuite();
        $notification = new Notification();
        $notification->setUser($user);
        $notification->setForum($forum);
        $notification->setSelfActivation($selfActivation);
        $this->om->persist($notification);
        $this->dispatch(new SubscribeForumEvent($forum));
        $this->om->endFlushSuite();
    }

    /**
     * Unsubscribe a user from a forum.
     *
     * @param \Claroline\ForumBundle\Entity\Forum $forum
     * @param \Claroline\CoreBundle\Entity\User   $user
     */
    public function unsubscribe(Forum $forum, User $user)
    {
        $this->om->startFlushSuite();
        $notification = $this->notificationRepo->findOneBy(['forum' => $forum, 'user' => $user]);
        $this->om->remove($notification);
        $this->dispatch(new UnsubscribeForumEvent($forum));
        $this->om->endFlushSuite();
    }

    /**
     * Create a category.
     *
     * @param \Claroline\ForumBundle\Entity\Forum $forum
     * @param string                              $name    The category name
     * @param bool                                $autolog
     *
     * @return \Claroline\ForumBundle\Entity\Category
     */
    public function createCategory(Forum $forum, $name, $autolog = true)
    {
        $this->om->startFlushSuite();
        $category = new Category();
        $category->setName($name);
        $category->setForum($forum);
        $this->om->persist($category);

        //required for the default category
        $this->om->persist($forum);

        //default category is not logged because the resource node doesn't exist yet
        if ($autolog) {
            $this->dispatch(new CreateCategoryEvent($category));
        }

        $this->om->endFlushSuite();

        return $category;
    }

    /**
     * @param \Claroline\ForumBundle\Entity\Category $category
     */
    public function deleteCategory(Category $category)
    {
        $this->om->startFlushSuite();
        $this->om->remove($category);
        $this->dispatch(new DeleteCategoryEvent($category));
        $this->om->endFlushSuite();
    }

    /**
     * @param \Claroline\ForumBundle\Entity\Message $message
     * @param \Claroline\ForumBundle\Entity\Subject $subject
     *
     * @throws \Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException
     *
     * @return \Claroline\ForumBundle\Entity\Message
     */
    public function createMessage(Message $message, Subject $subject)
    {
        $forum = $subject->getCategory()->getForum();
        $collection = new ResourceCollection([$forum->getResourceNode()]);

        if (!$this->authorization->isGranted('post', $collection)) {
            throw new AccessDeniedHttpException($collection->getErrorsForDisplay());
        }

        $user = $this->tokenStorage->getToken()->getUser();
        $message->setCreator($user);
        $message->setAuthor($user->getFirstName().' '.$user->getLastName());
        $message->setSubject($subject);
        $this->om->persist($message);
        $this->om->flush();
        $this->dispatch(new CreateMessageEvent($message));
        $this->sendMessageNotification($message, $message->getCreator());
        $this->resourceEvalManager->updateResourceUserEvaluationData(
            $forum->getResourceNode(),
            $user,
            new \DateTime(),
            AbstractResourceEvaluation::STATUS_PARTICIPATED
        );

        return $message;
    }

    /**
     * @param \Claroline\ForumBundle\Entity\Message $message
     */
    public function deleteMessage(Message $message)
    {
        $this->om->startFlushSuite();
        $this->om->remove($message);
        $this->dispatch(new DeleteMessageEvent($message));
        $this->om->endFlushSuite();
    }

    /**
     * @param \Claroline\ForumBundle\Entity\Subject $subject
     */
    public function deleteSubject(Subject $subject)
    {
        $this->om->startFlushSuite();
        $this->om->remove($subject);
        $this->dispatch(new DeleteSubjectEvent($subject));
        $this->om->endFlushSuite();
    }

    /**
     * @param \Claroline\ForumBundle\Entity\Subject $subject
     *
     * @return \Claroline\ForumBundle\Entity\Subject $subject
     */
    public function createSubject(Subject $subject)
    {
        $this->om->startFlushSuite();
        $this->om->persist($subject);
        $this->dispatch(new CreateSubjectEvent($subject));
        $this->om->endFlushSuite();

        return $subject;
    }

    /**
     * @param \Claroline\CoreBundle\Entity\User   $user
     * @param \Claroline\ForumBundle\Entity\Forum $forum
     *
     * @return bool
     */
    public function hasSubscribed(User $user, Forum $forum)
    {
        $notify = $this->notificationRepo->findBy(['user' => $user, 'forum' => $forum]);

        return count($notify) === 1 ? true : false;
    }

    /**
     * Send a notification to a user about a message.
     *
     * @param \Claroline\ForumBundle\Entity\Message $message
     * @param \Claroline\CoreBundle\Entity\User     $user
     */
    public function sendMessageNotification(Message $message, User $user)
    {
        $forum = $message->getSubject()->getCategory()->getForum();
        $notifications = $this->notificationRepo->findBy(['forum' => $forum]);
        $users = [];

        foreach ($notifications as $notification) {
            $users[] = $notification->getUser();
        }

        $title = $this->translator->trans(
            'forum_new_message',
            ['%forum%' => $forum->getResourceNode()->getName(), '%subject%' => $message->getSubject()->getTitle(), '%author%' => $message->getCreator()->getUsername()],
            'forum'
        );

        $url = $this->router->generate(
            'claro_forum_subjects', ['category' => $message->getSubject()->getCategory()->getId()], true
        );

        $body = "<a href='{$url}'>{$title}</a><hr>{$message->getContent()}";
        $this->mailManager->send($title, $body, $users);
    }

    /**
     * @param int $subjectId
     *
     * @return Subject
     */
    public function getSubject($subjectId)
    {
        return $this->subjectRepo->find($subjectId);
    }

    /**
     * @param int $forumId
     *
     * @return Forum
     */
    public function getForum($forumId)
    {
        return $this->forumRepo->find($forumId);
    }

    private function dispatch($event)
    {
        $this->dispatcher->dispatch('log', $event);

        return $this;
    }

    /**
     * Move a message to an other subject.
     *
     * @param \Claroline\ForumBundle\Entity\Message $message
     * @param \Claroline\ForumBundle\Entity\Subject $newSubject
     */
    public function moveMessage(Message $message, Subject $newSubject)
    {
        $this->om->startFlushSuite();
        $oldSubject = $message->getSubject();
        $message->setSubject($newSubject);
        $this->om->persist($message);
        $this->dispatch(new MoveMessageEvent($message, $oldSubject, $newSubject));
        $this->om->endFlushSuite();
    }

    /**
     * Move a subject to an other category.
     *
     * @param \Claroline\ForumBundle\Entity\Subject  $subject
     * @param \Claroline\ForumBundle\Entity\Category $newCategory
     */
    public function moveSubject(Subject $subject, Category $newCategory)
    {
        $this->om->startFlushSuite();
        $oldCategory = $subject->getCategory();
        $subject->setCategory($newCategory);
        $this->om->persist($subject);
        $this->dispatch(new MoveSubjectEvent($subject, $oldCategory, $newCategory));
        $this->om->endFlushSuite();
    }

    /**
     * Stick a subject at the top of the subject list.
     *
     * @param \Claroline\ForumBundle\Entity\Subject $subject
     */
    public function stickSubject(Subject $subject)
    {
        $this->om->startFlushSuite();
        $subject->setIsSticked(true);
        $this->om->persist($subject);
        $this->dispatch(new StickSubjectEvent($subject));
        $this->om->endFlushSuite();
    }

    /**
     * Unstick a subject from the top of the subject list.
     *
     * @param \Claroline\ForumBundle\Entity\Subject $subject
     */
    public function unstickSubject(Subject $subject)
    {
        $this->om->startFlushSuite();
        $subject->setIsSticked(false);
        $this->om->persist($subject);
        $this->dispatch(new UnstickSubjectEvent($subject));
        $this->om->endFlushSuite();
    }

    /**
     * Close a subject and no one can write in it.
     *
     * @param \Claroline\ForumBundle\Entity\Subject $subject
     */
    public function closeSubject(Subject $subject)
    {
        $this->om->startFlushSuite();
        $subject->setIsClosed(true);
        $this->om->persist($subject);
        $this->dispatch(new CloseSubjectEvent($subject));
        $this->om->endFlushSuite();
    }

    /**
     * Open a subject.
     *
     * @param \Claroline\ForumBundle\Entity\Subject $subject
     */
    public function openSubject(Subject $subject)
    {
        $this->om->startFlushSuite();
        $subject->setIsClosed(false);
        $this->om->persist($subject);
        $this->dispatch(new OpenSubjectEvent($subject));
        $this->om->endFlushSuite();
    }

    /**
     * Get the pager for the subject list of a category.
     *
     * @param \Claroline\ForumBundle\Entity\Category $category
     * @param int                                    $page
     * @param int                                    $max
     *
     * @return \Pagerfanta\Pagerfanta
     */
    public function getSubjectsPager(Category $category, $page = 1, $max = 20)
    {
        $subjects = $this->forumRepo->findSubjects($category);

        return $this->pagerFactory->createPagerFromArray($subjects, $page, $max);
    }

    /**
     * Get the pager for the message list of a subject.
     *
     * @param \Claroline\ForumBundle\Entity\Subject $subject
     * @param int                                   $page
     * @param int                                   $max
     *
     * @return \Pagerfanta\Pagerfanta
     */
    public function getMessagesPager(Subject $subject, $page = 1, $max = 20)
    {
        $messages = $this->messageRepo->findBySubject($subject);

        return $this->pagerFactory->createPagerFromArray($messages, $page, $max);
    }

    /**
     * Get the pager for the forum search.
     *
     * @param \Claroline\ForumBundle\Entity\Forum $forum
     * @param string                              $search
     * @param int                                 $page
     *
     * @return \Pagerfanta\Pagerfanta
     */
    public function searchPager(Forum $forum, $search, $page)
    {
        $query = $this->forumRepo->search($forum, $search);

        return $this->pagerFactory->createPager($query, $page);
    }

    /**
     * @param \Claroline\ForumBundle\Entity\Message $message
     * @param string                                $oldContent
     * @param string                                $newContent
     */
    public function editMessage(Message $message, $oldContent, $newContent)
    {
        $this->om->startFlushSuite();
        $message->setContent($newContent);
        $this->om->persist($message);
        $this->dispatch(new EditMessageEvent($message, $oldContent, $newContent));
        $this->om->endFlushSuite();
    }

    /**
     * @param \Claroline\ForumBundle\Entity\Subject $subject
     * @param string                                $oldTitle
     * @param string                                $newTitle
     */
    public function editSubject(Subject $subject, $oldTitle, $newTitle)
    {
        $this->om->startFlushSuite();
        $subject->setTitle($newTitle);
        $this->om->persist($subject);
        $this->dispatch(new EditSubjectEvent($subject, $oldTitle, $newTitle));
        $this->om->endFlushSuite();
    }

    /**
     * @param \Claroline\ForumBundle\Entity\Category $category
     * @param string                                 $oldName
     * @param string                                 $newName
     */
    public function editCategory(Category $category, $oldName, $newName)
    {
        $this->om->startFlushSuite();
        $category->setName($newName);
        $this->om->persist($category);
        $this->dispatch(new EditCategoryEvent($category, $oldName, $newName));
        $this->om->endFlushSuite();
    }

    public function copy(Forum $forum)
    {
        $newForum = new Forum();
        $forum->setName($forum->getName());
        $oldCategories = $forum->getCategories();
        $this->om->persist($newForum);

        foreach ($oldCategories as $oldCategory) {
            $newCategory = new Category();
            $newCategory->setName($oldCategory->getName());
            $newCategory->setForum($newForum);
            $newCategory->setCreationDate($oldCategory->getCreationDate());
            $newCategory->setModificationDate($oldCategory->getModificationDate());
            $oldSubjects = $oldCategory->getSubjects();

            foreach ($oldSubjects as $oldSubject) {
                $newSubject = new Subject();
                $newSubject->setTitle($oldSubject->getTitle());
                $newSubject->setCreator($oldSubject->getCreator());
                $newSubject->setCategory($newCategory);
                $newSubject->setCreationDate($oldSubject->getCreationDate());
                $newSubject->setModificationDate($oldSubject->getModificationDate());
                $newSubject->setIsSticked($oldSubject->isSticked());
                $oldMessages = $oldSubject->getMessages();

                foreach ($oldMessages as $oldMessage) {
                    $newMessage = new Message();
                    $newMessage->setSubject($newSubject);
                    $newMessage->setCreator($oldMessage->getCreator());
                    $newMessage->setContent($oldMessage->getContent());
                    $newMessage->setCreationDate($oldMessage->getCreationDate());
                    $newMessage->setModificationDate($oldMessage->getModificationDate());

                    $this->om->persist($newMessage);
                }

                $this->om->persist($newSubject);
            }

            $this->om->persist($newCategory);
        }

        return $newForum;
    }

    public function getMessageQuoteHTML(Message $message)
    {
        $answer = $this->translator->trans('answer_message', [], 'forum');
        $author = $message->getCreator()->getFirstName()
            .' '
            .$message->getCreator()->getLastName();
        $date = $message->getCreationDate()->format($this->translator->trans('date_range.format.with_hours', [], 'platform'));
        $by = $this->translator->trans('posted_by', ['%author%' => $author, '%date%' => $date], 'forum');
        $mask = '<div class="original-poster"><b>'.$by.'</b></div><div class="well">%s</div></div><b>'.$answer.':</b></div>';

        return sprintf(
            $mask,
            $message->getContent()
        );
    }

    public function getReplyHTML(Message $message)
    {
        $author = $message->getCreator()->getFirstName()
            .' '
            .$message->getCreator()->getLastName();
        $date = $message->getCreationDate()->format($this->translator->trans('date_range.format.with_hours', [], 'platform'));
        $by = $this->translator->trans('posted_by', ['%author%' => $author, '%date%' => $date], 'forum');

        return $by;
    }

    public function activateGlobalNotifications(Forum $forum)
    {
        $this->om->startFlushSuite();
        $forum->setActivateNotifications(true);
        $this->om->persist($forum);
        $node = $forum->getResourceNode();
        $roles = $this->roleRepo->findRolesWithRightsByResourceNode($node);
        $usersWithRoles = $this->userRepo->findUsersByRolesIncludingGroups($roles);
        $users = $this->forumRepo
            ->findUnnotifiedUsersFromListByForum($forum, $usersWithRoles);

        foreach ($users as $user) {
            $this->subscribe($forum, $user, false);
        }
        $this->om->endFlushSuite();
    }

    public function disableGlobalNotifications(Forum $forum)
    {
        $this->om->startFlushSuite();
        $forum->setActivateNotifications(false);
        $this->om->persist($forum);
        $notifications = $this->forumRepo->findNonSelfNotificationsByForum($forum);

        foreach ($notifications as $notification) {
            $this->removeNotification($forum, $notification);
        }
        $this->om->endFlushSuite();
    }

    public function getLastMessagesBySubjectsIds(array $subjectsIds)
    {
        $lastMessages = [];

        if (count($subjectsIds) > 0) {
            $lastMessages = $this->forumRepo
                ->findLastMessagesBySubjectsIds($subjectsIds);
        }

        return $lastMessages;
    }

    /**
     * Unsubscribe a user from a forum.
     *
     * @param \Claroline\ForumBundle\Entity\Forum        $forum
     * @param \Claroline\ForumBundle\Entity\Notification $notification
     */
    private function removeNotification(Forum $forum, Notification $notification)
    {
        $this->om->startFlushSuite();
        $this->om->remove($notification);
        $this->dispatch(new UnsubscribeForumEvent($forum));
        $this->om->endFlushSuite();
    }

    public function createDefaultPostRights(ResourceNode $node)
    {
        $workspace = $node->getWorkspace();
        $resourceType = $node->getResourceType();
        $role = $this->roleRepo->findOneBaseWorkspaceRole('COLLABORATOR', $workspace);

        if (!is_null($role)) {
            $postDecoder = $this->maskManager->getDecoder($resourceType, 'post');

            if (!is_null($postDecoder)) {
                $rights = $this->rightsManager->getOneByRoleAndResource($role, $node);
                $value = $postDecoder->getValue();
                $mask = $rights->getMask();
                $permissions = $mask | $value;
                $this->rightsManager->editPerms($permissions, $role, $node);
            }
        }
    }

    public function getSubjectsReadingLogs(
        User $user,
        ResourceNode $node,
        $orderedBy = 'id',
        $order = 'DESC'
    ) {
        return $this->forumRepo->findSubjectsReadingLogs(
            $user,
            $node,
            $orderedBy,
            $order
        );
    }

    /**
     * @param array $roles
     * @param int   $max
     * @param array $subjects
     *
     * @return \Claroline\ForumBundle\Entity\Message[]
     */
    public function getLastMessagesByRoles(array $roles, $max = 10, $subjects = [])
    {
        return count($subjects) > 0 ?
            $this->messageRepo->findNLastByRolesAndSubjects($roles, $subjects, $max) :
            $this->messageRepo->findNLastByRoles($roles, $max);
    }

    /**
     * @param array $workspaces
     * @param array $roles
     * @param int   $max
     * @param array $subjects
     *
     * @return \Claroline\ForumBundle\Entity\Message[]
     */
    public function getLastMessagesByWorkspacesAndRoles(array $workspaces, array $roles, $max = 10, $subjects = [])
    {
        return count($subjects) > 0 ?
            $this->messageRepo->findNLastByWorkspacesAndRolesAndSubjects($workspaces, $roles, $subjects, $max) :
            $this->messageRepo->findNLastByWorkspacesAndRoles($workspaces, $roles, $max);
    }

    /**
     * @param Forum $forum
     * @param array $roles
     * @param int   $max
     * @param array $subjects
     *
     * @return \Claroline\ForumBundle\Entity\Message[]
     */
    public function getLastMessagesByForumAndRoles(Forum $forum, array $roles, $max = 10, $subjects = [])
    {
        return count($subjects) > 0 ?
            $this->messageRepo->findNLastByForumAndRolesAndSubjects($forum, $roles, $subjects, $max) :
            $this->messageRepo->findNLastByForumAndRoles($forum, $roles, $max);
    }

    /**
     * @param WidgetInstance $widgetInstance
     *
     * @return \Claroline\ForumBundle\Entity\Widget\LastMessageWidgetConfig
     */
    public function getConfig(WidgetInstance $widgetInstance)
    {
        $lastMessageWidgetConfig = $this->lastMessageWidgetConfigRepo->findOneOrNullByWidgetInstance($widgetInstance);

        if ($lastMessageWidgetConfig === null) {
            $lastMessageWidgetConfig = new LastMessageWidgetConfig();
            $lastMessageWidgetConfig->setWidgetInstance($widgetInstance);
        }

        return $lastMessageWidgetConfig;
    }

    /**
     * @param WidgetInstance $widgetInstance
     *
     * @return \Claroline\ForumBundle\Entity\Message[]
     */
    public function getLastMessages(WidgetInstance $widgetInstance)
    {
        $workspace = $widgetInstance->getWorkspace();
        $config = $this->getConfig($widgetInstance);
        $forum = null;
        $participateOnly = false;
        $mySubjects = [];

        if (!is_null($config)) {
            $resourceNode = $config->getForum();
            $participateOnly = $config->getDisplayMyLastMessages();

            if (!is_null($resourceNode)) {
                $forum = $this->resourceManager->getResourceFromNode($resourceNode);
            }
        }
        $token = $this->tokenStorage->getToken();
        $roles = $this->securityUtilities->getRoles($token);

        if ($participateOnly && $token->getUser() instanceof User) {
            $mySubjects = $this->getSubjectsByParticipant($token->getUser());
        }

        if (is_null($forum)) {
            $messages = is_null($workspace) ?
                $this->getLastMessagesByRoles($roles, 10, $mySubjects) :
                $this->getLastMessagesByWorkspacesAndRoles([$workspace], $roles, 10, $mySubjects);
        } else {
            $messages = $this->getLastMessagesByForumAndRoles($forum, $roles, 10, $mySubjects);
        }

        return $messages;
    }

    public function persistLastMessageWidgetConfig(LastMessageWidgetConfig $config)
    {
        $this->om->persist($config);
        $this->om->flush();
    }

    public function getSubjectsByParticipant(User $user)
    {
        return $this->subjectRepo->findSubjectsByParticipant($user);
    }
}
