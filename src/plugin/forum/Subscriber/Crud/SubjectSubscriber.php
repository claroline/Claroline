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
use Claroline\CoreBundle\Manager\FileManager;
use Claroline\CoreBundle\Security\PermissionCheckerTrait;
use Claroline\ForumBundle\Entity\Forum;
use Claroline\ForumBundle\Entity\Subject;
use Claroline\ForumBundle\Entity\Validation\User as UserValidation;
use Claroline\ForumBundle\Event\LogSubjectEvent;
use Claroline\ForumBundle\Manager\ForumManager;
use Claroline\ForumBundle\Messenger\NotifyUsersOnMessageCreated;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

class SubjectSubscriber implements EventSubscriberInterface
{
    use PermissionCheckerTrait;

    /** @var AuthorizationCheckerInterface */
    private $authorization;
    /** @var MessageBusInterface */
    private $messageBus;
    private $dispatcher;
    /** @var ObjectManager */
    private $om;
    private $finder;
    /** @var ForumManager */
    private $forumManager;
    /** @var FileManager */
    private $fileManager;

    public function __construct(
        AuthorizationCheckerInterface $authorization,
        MessageBusInterface $messageBus,
        StrictDispatcher $dispatcher,
        ObjectManager $om,
        FinderProvider $finder,
        ForumManager $forumManager,
        FileManager $fileManager
    ) {
        $this->authorization = $authorization;
        $this->messageBus = $messageBus;
        $this->dispatcher = $dispatcher;
        $this->om = $om;
        $this->finder = $finder;
        $this->forumManager = $forumManager;
        $this->fileManager = $fileManager;
    }

    public static function getSubscribedEvents()
    {
        return [
            Crud::getEventName('create', 'pre', Subject::class) => 'preCreate',
            Crud::getEventName('create', 'post', Subject::class) => 'postCreate',
            Crud::getEventName('update', 'post', Subject::class) => 'postUpdate',
            Crud::getEventName('delete', 'post', Subject::class) => 'postDelete',
        ];
    }

    public function preCreate(CreateEvent $event)
    {
        /** @var Subject $subject */
        $subject = $event->getObject();
        $forum = $subject->getForum();

        //create user if not here
        $user = $this->om->getRepository(UserValidation::class)->findOneBy([
            'user' => $subject->getCreator(),
            'forum' => $forum,
        ]);

        if (!$user) {
            $user = new UserValidation();
            $user->setForum($forum);
            $user->setUser($subject->getCreator());
        }

        $messages = $subject->getMessages();
        $first = $messages && isset($messages[0]) ? $messages[0] : null;

        if (!$this->checkPermission('EDIT', $forum->getResourceNode())) {
            if (Forum::VALIDATE_PRIOR_ALL === $forum->getValidationMode()) {
                $subject->setModerated(Forum::VALIDATE_PRIOR_ALL);
                if ($first) {
                    $first->setModerated(Forum::VALIDATE_PRIOR_ALL);
                }
            }

            if (Forum::VALIDATE_PRIOR_ONCE === $forum->getValidationMode()) {
                $subject->setModerated($user->getAccess() ? Forum::VALIDATE_NONE : Forum::VALIDATE_PRIOR_ONCE);
                if ($first) {
                    $first->setModerated(Forum::VALIDATE_PRIOR_ALL);
                }
            }
        } else {
            $subject->setModerated(Forum::VALIDATE_NONE);
            if ($first) {
                $first->setModerated(Forum::VALIDATE_PRIOR_ALL);
            }
        }

        if ($first) {
            $this->om->persist($first);
        }
    }

    /**
     * Send notifications after creation.
     */
    public function postCreate(CreateEvent $event)
    {
        /** @var Subject $subject */
        $subject = $event->getObject();

        if ($subject->getPoster()) {
            $this->fileManager->linkFile(Subject::class, $subject->getUuid(), $subject->getPoster()->getUrl());
        }

        $message = $subject->getFirstMessage();
        if ($message) {
            // hacky : when we are in a flushSuite (eg. copy), the messenger will fail because the message does not exist
            $this->om->forceFlush();
            $this->messageBus->dispatch(new NotifyUsersOnMessageCreated($message->getId()));
        }

        $this->dispatchSubjectEvent($subject, 'forum_subject-create');
    }

    public function postUpdate(UpdateEvent $event)
    {
        /** @var Subject $subject */
        $subject = $event->getObject();
        $oldData = $event->getOldData();

        $this->fileManager->updateFile(
            Subject::class,
            $subject->getUuid(),
            !empty($subject->getPoster()) ? $subject->getPoster()->getUrl() : null,
            !empty($oldData['poster']) ? $oldData['poster']['url'] : null
        );

        if ($oldData['meta']['flagged'] !== $subject->isFlagged()) {
            if ($subject->isFlagged()) {
                $this->dispatchSubjectEvent($subject, 'forum_subject-flag');
            } else {
                $this->dispatchSubjectEvent($subject, 'forum_subject-unflag');
            }
        }

        if ($oldData['meta']['closed'] !== $subject->isClosed()) {
            if ($subject->isClosed()) {
                $this->dispatchSubjectEvent($subject, 'forum_subject-close');
            } else {
                $this->dispatchSubjectEvent($subject, 'forum_subject-open');
            }
        }

        if ($oldData['meta']['sticky'] !== $subject->isSticked()) {
            if ($subject->isSticked()) {
                $this->dispatchSubjectEvent($subject, 'forum_subject-stick');
            } else {
                $this->dispatchSubjectEvent($subject, 'forum_subject-unstick');
            }
        }

        if ($oldData['meta']['moderation'] !== $subject->getModerated()) {
            if (Forum::VALIDATE_NONE === $subject->getModerated()) {
                $this->dispatchSubjectEvent($subject, 'forum_subject-unmoderated');
            }
        }

        $this->dispatchSubjectEvent($subject, 'forum_subject-update');
    }

    public function postDelete(DeleteEvent $event)
    {
        /** @var Subject $subject */
        $subject = $event->getObject();

        if ($subject->getPoster()) {
            $this->fileManager->unlinkFile(Subject::class, $subject->getUuid(), $subject->getPoster()->getUrl());
        }

        $this->dispatchSubjectEvent($subject, 'forum_subject-delete');
    }

    /**
     * @deprecated
     */
    private function dispatchSubjectEvent(Subject $subject, $action)
    {
        $forum = $subject->getForum();

        $usersToNotify = $this->finder->get(User::class)->find(['workspace' => $forum->getResourceNode()->getWorkspace()->getUuid()]);
        $this->dispatcher->dispatch('log', LogSubjectEvent::class, [$action, $subject, $usersToNotify]);
    }
}
