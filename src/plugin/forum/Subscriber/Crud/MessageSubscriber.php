<?php

namespace Claroline\ForumBundle\Subscriber\Crud;

use Claroline\AppBundle\API\Crud;
use Claroline\AppBundle\API\FinderProvider;
use Claroline\AppBundle\Event\Crud\CreateEvent;
use Claroline\AppBundle\Event\Crud\DeleteEvent;
use Claroline\AppBundle\Event\Crud\UpdateEvent;
use Claroline\AppBundle\Event\StrictDispatcher;
use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Security\PermissionCheckerTrait;
use Claroline\ForumBundle\Entity\Forum;
use Claroline\ForumBundle\Entity\Message;
use Claroline\ForumBundle\Entity\Validation\User as UserValidation;
use Claroline\ForumBundle\Event\LogMessageEvent;
use Claroline\ForumBundle\Manager\ForumManager;
use Claroline\ForumBundle\Messenger\NotifyUsersOnMessageCreated;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

class MessageSubscriber implements EventSubscriberInterface
{
    use PermissionCheckerTrait;

    /** @var StrictDispatcher */
    private $dispatcher;
    /** @var ObjectManager */
    private $om;
    /** @var FinderProvider */
    private $finder;
    /** @var ForumManager */
    private $forumManager;
    /** @var MessageBusInterface */
    private $messageBus;

    public function __construct(
        StrictDispatcher $dispatcher,
        ObjectManager $om,
        FinderProvider $finder,
        ForumManager $forumManager,
        AuthorizationCheckerInterface $authorization,
        MessageBusInterface $messageBus
    ) {
        $this->dispatcher = $dispatcher;
        $this->om = $om;
        $this->finder = $finder;
        $this->forumManager = $forumManager;
        $this->authorization = $authorization;
        $this->messageBus = $messageBus;
    }

    public static function getSubscribedEvents()
    {
        return [
            Crud::getEventName('create', 'pre', Message::class) => 'preCreate',
            Crud::getEventName('create', 'post', Message::class) => 'postCreate',
            Crud::getEventName('update', 'post', Message::class) => 'postUpdate',
            Crud::getEventName('delete', 'post', Message::class) => 'postDelete',
        ];
    }

    /**
     * Manage moderation on message create.
     */
    public function preCreate(CreateEvent $event)
    {
        /** @var Message $message */
        $message = $event->getObject();
        $forum = $message->getSubject()->getForum();

        //create user if not here
        $user = $this->om->getRepository(UserValidation::class)->findOneBy([
            'user' => $message->getCreator(),
            'forum' => $forum,
        ]);

        if (!$user) {
            $user = new UserValidation();
            $user->setForum($forum);
            $user->setUser($message->getCreator());
        }
        if (!$this->checkPermission('EDIT', $forum->getResourceNode())) {
            if (Forum::VALIDATE_PRIOR_ALL === $forum->getValidationMode()) {
                $message->setModerated(Forum::VALIDATE_PRIOR_ALL);
            }

            if (Forum::VALIDATE_PRIOR_ONCE === $forum->getValidationMode()) {
                $message->setModerated($user->getAccess() ? Forum::VALIDATE_NONE : Forum::VALIDATE_PRIOR_ONCE);
            }
        } else {
            $message->setModerated(Forum::VALIDATE_NONE);
        }
    }

    /**
     * Send notifications after creation.
     */
    public function postCreate(CreateEvent $event)
    {
        /** @var Message $message */
        $message = $event->getObject();

        $this->messageBus->dispatch(new NotifyUsersOnMessageCreated($message->getId()));

        $this->dispatchMessageEvent($message, 'forum_message-create');
    }

    public function postUpdate(UpdateEvent $event)
    {
        //c'est ici aussi qu'on catch le flag d'un message
        $message = $event->getObject();

        $old = $event->getOldData();

        if ($old['meta']['flagged'] !== $message->isFlagged()) {
            if ($message->isFlagged()) {
                $this->dispatchMessageEvent($message, 'forum_message-flag');
            } else {
                $this->dispatchMessageEvent($message, 'forum_message-unflag');
            }
        }

        if ($old['meta']['moderation'] !== $message->getModerated()) {
            if (Forum::VALIDATE_NONE === $message->getModerated()) {
                $this->dispatchMessageEvent($message, 'forum_message-unmoderated');
            }
        }
    }

    public function postDelete(DeleteEvent $event)
    {
        /** @var Message $message */
        $message = $event->getObject();

        $this->dispatchMessageEvent($message, 'forum_message-delete');
    }

    /**
     * @deprecated
     */
    private function dispatchMessageEvent(Message $message, $action)
    {
        $forum = $this->getSubject($message)->getForum();

        $usersToNotify = $this->finder->get(User::class)->find(['workspace' => $forum->getResourceNode()->getWorkspace()->getUuid()]);
        $this->dispatcher->dispatch('log', LogMessageEvent::class, [$action, $message, $usersToNotify]);
    }

    private function getSubject(Message $message)
    {
        if (!$message->getSubject()) {
            $parent = $message->getParent();

            return $this->getSubject($parent);
        }

        return $message->getSubject();
    }
}
