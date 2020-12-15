<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\ForumBundle\Listener;

use Claroline\AppBundle\Event\Crud\CreateEvent;
use Claroline\AppBundle\Event\Crud\DeleteEvent;
use Claroline\AppBundle\Event\Crud\UpdateEvent;
use Claroline\AppBundle\Event\StrictDispatcher;
use Claroline\CoreBundle\API\Finder\User\UserFinder;
use Claroline\ForumBundle\Entity\Forum;
use Claroline\ForumBundle\Entity\Message;
use Claroline\ForumBundle\Entity\Subject;
use Claroline\ForumBundle\Event\LogMessageEvent;
use Claroline\ForumBundle\Event\LogSubjectEvent;

class CrudListener
{
    public function __construct(StrictDispatcher $dispatcher, UserFinder $userFinder)
    {
        $this->dispatcher = $dispatcher;
        $this->userFinder = $userFinder;
    }

    public function onPostCreate(CreateEvent $event)
    {
        $message = $event->getObject();

        $this->dispatchMessageEvent($message, 'forum_message-create');
    }

    public function onPostUpdate(UpdateEvent $event)
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

    public function onPostDelete(DeleteEvent $event)
    {
        $message = $event->getObject();

        $this->dispatchMessageEvent($message, 'forum_message-delete');
    }

    public function onSubjectCreate(CreateEvent $event)
    {
        $subject = $event->getObject();

        $this->dispatchSubjectEvent($subject, 'forum_subject-create');
    }

    public function onSubjectUpdate(UpdateEvent $event)
    {
        //c'est ici aussi qu'on catch le flag d'un sujet
        $subject = $event->getObject();

        $old = $event->getOldData();

        if ($old['meta']['flagged'] !== $subject->isFlagged()) {
            if ($subject->isFlagged()) {
                $this->dispatchSubjectEvent($subject, 'forum_subject-flag');
            } else {
                $this->dispatchSubjectEvent($subject, 'forum_subject-unflag');
            }
        }

        if ($old['meta']['closed'] !== $subject->isClosed()) {
            if ($subject->isClosed()) {
                $this->dispatchSubjectEvent($subject, 'forum_subject-close');
            } else {
                $this->dispatchSubjectEvent($subject, 'forum_subject-open');
            }
        }

        if ($old['meta']['sticky'] !== $subject->isSticked()) {
            if ($subject->isSticked()) {
                $this->dispatchSubjectEvent($subject, 'forum_subject-stick');
            } else {
                $this->dispatchSubjectEvent($subject, 'forum_subject-unstick');
            }
        }

        if ($old['meta']['moderation'] !== $subject->getModerated()) {
            if (Forum::VALIDATE_NONE === $subject->getModerated()) {
                $this->dispatchSubjectEvent($subject, 'forum_subject-unmoderated');
            }
        }

        $this->dispatchSubjectEvent($subject, 'forum_subject-update');
    }

    public function onSubjectDelete(DeleteEvent $event)
    {
        $subject = $event->getObject();

        $this->dispatchSubjectEvent($subject, 'forum_subject-delete');
    }

    private function dispatchMessageEvent(Message $message, $action)
    {
        $forum = $this->getSubject($message)->getForum();

        $usersToNotify = $this->userFinder->find(['workspace' => $forum->getResourceNode()->getWorkspace()->getUuid()]);
        $this->dispatcher->dispatch('log', LogMessageEvent::class, [$action, $message, $usersToNotify]);
    }

    private function dispatchSubjectEvent(Subject $subject, $action)
    {
        $forum = $subject->getForum();

        $usersToNotify = $this->userFinder->find(['workspace' => $forum->getResourceNode()->getWorkspace()->getUuid()]);
        $this->dispatcher->dispatch('log', LogSubjectEvent::class, [$action, $subject, $usersToNotify]);
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
