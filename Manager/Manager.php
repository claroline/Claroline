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

use Claroline\CoreBundle\Entity\Resource\ResourceNode;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Library\Resource\ResourceCollection;
use Claroline\CoreBundle\Manager\MailManager;
use Claroline\CoreBundle\Manager\MaskManager;
use Claroline\CoreBundle\Manager\RightsManager;
use Claroline\CoreBundle\Pager\PagerFactory;
use Claroline\CoreBundle\Persistence\ObjectManager;
use Claroline\ForumBundle\Entity\Category;
use Claroline\ForumBundle\Entity\Forum;
use Claroline\ForumBundle\Entity\Message;
use Claroline\ForumBundle\Entity\Notification;
use Claroline\ForumBundle\Entity\Subject;
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
use Symfony\Component\Security\Core\SecurityContextInterface;
use Symfony\Component\Translation\TranslatorInterface;

/**
 * @DI\Service("claroline.manager.forum_manager")
 */
class Manager
{
    private $om;
    private $pagerFactory;
    private $dispatcher;
    private $notificationRepo;
    private $subjectRepo;
    private $messageRepo;
    private $forumRepo;
    private $roleRepo;
    private $userRepo;
    private $messageManager;
    private $translator;
    private $router;
    private $mailManager;
    private $container;
    private $sc;
    private $maskManager;
    private $rightsManager;

    /**
     * Constructor.
     *
     * @DI\InjectParams({
     *     "om"             = @DI\Inject("claroline.persistence.object_manager"),
     *     "pagerFactory"   = @DI\Inject("claroline.pager.pager_factory"),
     *     "dispatcher"     = @DI\Inject("event_dispatcher"),
     *     "messageManager" = @DI\Inject("claroline.manager.message_manager"),
     *     "translator"     = @DI\Inject("translator"),
     *     "router"         = @DI\Inject("router"),
     *     "mailManager"    = @DI\Inject("claroline.manager.mail_manager"),
     *     "container"      = @DI\Inject("service_container"),
     *     "sc"             = @DI\Inject("security.context"),
     *     "maskManager"    = @DI\Inject("claroline.manager.mask_manager"),
     *     "rightsManager"  = @DI\Inject("claroline.manager.rights_manager")
     * })
     */
    public function __construct(
        ObjectManager $om,
        PagerFactory $pagerFactory,
        EventDispatcherInterface $dispatcher,
        MessageManager $messageManager,
        TranslatorInterface $translator,
        RouterInterface $router,
        MailManager $mailManager,
        ContainerInterface $container,
        SecurityContextInterface $sc,
        MaskManager $maskManager,
        RightsManager $rightsManager
    )
    {
        $this->om = $om;
        $this->pagerFactory = $pagerFactory;
        $this->notificationRepo = $om->getRepository('ClarolineForumBundle:Notification');
        $this->subjectRepo = $om->getRepository('ClarolineForumBundle:Subject');
        $this->messageRepo = $om->getRepository('ClarolineForumBundle:Message');
        $this->forumRepo = $om->getRepository('ClarolineForumBundle:Forum');
        $this->roleRepo = $om->getRepository('ClarolineCoreBundle:Role');
        $this->userRepo = $om->getRepository('ClarolineCoreBundle:User');
        $this->dispatcher = $dispatcher;
        $this->messageManager = $messageManager;
        $this->translator = $translator;
        $this->router = $router;
        $this->mailManager = $mailManager;
        $this->container = $container;
        $this->sc = $sc;
        $this->maskManager = $maskManager;
        $this->rightsManager = $rightsManager;
    }

    /**
     * Subscribe a user to a forum. A mail will be sent to the user each time
     * a message is posted.
     *
     * @param \Claroline\ForumBundle\Entity\Forum $forum
     * @param \Claroline\CoreBundle\Entity\User $user
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
     * @param \Claroline\CoreBundle\Entity\User $user
     */
    public function unsubscribe(Forum $forum, User $user)
    {
        $this->om->startFlushSuite();
        $notification = $this->notificationRepo->findOneBy(array('forum' => $forum, 'user' => $user));
        $this->om->remove($notification);
        $this->dispatch(new UnsubscribeForumEvent($forum));
        $this->om->endFlushSuite();
    }

    /**
     * Create a category.
     *
     * @param \Claroline\ForumBundle\Entity\Forum $forum
     * @param string $name The category name
     * @param boolean $autolog
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
     *
     * @param \Claroline\ForumBundle\Entity\Subject $subject
     * @throws \Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException
     * @return \Claroline\ForumBundle\Entity\Message
     */
    public function createMessage(Message $message, Subject $subject)
    {
    	$forum = $subject->getCategory()->getForum();
        $collection = new ResourceCollection(array($forum->getResourceNode()));

        if (!$this->sc->isGranted('post', $collection)) {
            throw new AccessDeniedHttpException($collection->getErrorsForDisplay());
        }

     	$user = $this->sc->getToken()->getUser();
        $message->setCreator($user);
        $message->setAuthor($user->getFirstName() . ' ' . $user->getLastName());
        $message->setSubject($subject);
        $this->om->persist($message);
        $this->om->flush();
		$this->dispatch(new CreateMessageEvent($message));
        $this->sendMessageNotification($message, $message->getCreator());

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
     * @param \Claroline\CoreBundle\Entity\User $user
     * @param \Claroline\ForumBundle\Entity\Forum $forum
     * @return boolean
     */
    public function hasSubscribed(User $user, Forum $forum)
    {
        $notify = $this->notificationRepo->findBy(array('user' => $user, 'forum' => $forum));

        return count($notify) === 1 ? true : false;
    }

    /**
     * Send a notification to a user about a message.
     *
     * @param \Claroline\ForumBundle\Entity\Message $message
     * @param \Claroline\CoreBundle\Entity\User $user
     */
    public function sendMessageNotification(Message $message, User $user)
    {
        $forum = $message->getSubject()->getCategory()->getForum();
        $notifications = $this->notificationRepo->findBy(array('forum' => $forum));
        $users = array();

        foreach ($notifications as $notification) {
            $users[] = $notification->getUser();
        }

        $title = $this->translator->trans(
            'forum_new_message',
            array('%forum%' => $forum->getResourceNode()->getName(), '%subject%' => $message->getSubject()->getTitle(), '%author%' => $message->getCreator()->getUsername()),
            'forum'
        );

        $url = $this->router->generate(
            'claro_forum_subjects', array('category' => $message->getSubject()->getCategory()->getId()), true
        );

        $body = "<a href='{$url}'>{$title}</a><hr>{$message->getContent()}";

        $this->mailManager->send($title, $body, $users);
    }

    /**
     * @param integer $subjectId
     *
     * @return Subject
     */
    public function getSubject($subjectId)
    {
        return $this->subjectRepo->find($subjectId);
    }

    /**
     * @param integer $forumId
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
     * @param \Claroline\ForumBundle\Entity\Subject $subject
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
     * @param integer $page
     * @param integer $max
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
     * @param integer $page
     * @param integer $max
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
     * @param string $search
     * @param integer $page
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
     * @param string $oldContent
     * @param string $newContent
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
     * @param string $oldTitle
     * @param string $newTitle
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
     * @param string $oldName
     * @param string $newName
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
        $answer = $this->translator->trans('answer_message', array(), 'forum');
        $author = $message->getCreator()->getFirstName()
            . ' '
            . $message->getCreator()->getLastName();
        $date = $message->getCreationDate()->format($this->translator->trans('date_range.format.with_hours', array(), 'platform'));
        $by = $this->translator->trans('posted_by', array('%author%' => $author, '%date%' => $date), 'forum');
        $mask = '<div class="original-poster"><b>' . $by . '</b></div><div class="well">%s</div></div><b>' . $answer . ':</b></div>';

        return sprintf(
            $mask,
            $message->getContent()
        );
    }
    
    public function getReplyHTML(Message $message)
    {
        $author = $message->getCreator()->getFirstName()
            . ' '
            . $message->getCreator()->getLastName();
        $date = $message->getCreationDate()->format($this->translator->trans('date_range.format.with_hours', array(), 'platform'));
        $by = $this->translator->trans('posted_by', array('%author%' => $author, '%date%' => $date), 'forum');
        
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
        $lastMessages = array();

        if (count($subjectsIds) > 0) {
            $lastMessages = $this->forumRepo
                ->findLastMessagesBySubjectsIds($subjectsIds);
        }

        return $lastMessages;
    }

    /**
     * Unsubscribe a user from a forum.
     *
     * @param \Claroline\ForumBundle\Entity\Forum $forum
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
}
