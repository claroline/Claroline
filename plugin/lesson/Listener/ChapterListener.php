<?php

/*
* This file is part of the Claroline Connect package.
*
* (c) Claroline Consortium <consortium@claroline.net>
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*/

namespace Icap\LessonBundle\Listener;

use Doctrine\ORM\Event\LifecycleEventArgs;
use Icap\LessonBundle\Entity\Chapter;
use Icap\NotificationBundle\Entity\UserPickerContent;
use Icap\NotificationBundle\Manager\NotificationManager as NotificationManager;

class ChapterListener
{
    /** @var \Icap\NotificationBundle\Manager\NotificationManager */
    private $notificationManager;

    public function __construct(NotificationManager $notificationManager)
    {
        $this->notificationManager = $notificationManager;
    }

    public function postPersist(Chapter $chapter, LifecycleEventArgs $event)
    {
        $userPicker = $chapter->getUserPicker();
        $lesson = $chapter->getLesson();
        if (
            null !== $userPicker &&
            count($userPicker->getUserIds()) > 0 &&
            null !== $lesson->getResourceNode()
        ) {
            $details = [
                'chapter' => [
                    'lesson' => $lesson->getId(),
                    'chapter' => $chapter->getId(),
                    'title' => $chapter->getTitle(),
                ],
                'resource' => [
                    'id' => $lesson->getId(),
                    'name' => $lesson->getResourceNode()->getName(),
                    'type' => $lesson->getResourceNode()->getResourceType()->getName(),
                ],
            ];
            $notification = $this->notificationManager->createNotification(
                'resource-icap_lesson-user_tagged',
                'lesson',
                $lesson->getResourceNode()->getId(),
                $details
            );
            $this->notificationManager->notifyUsers($notification, $userPicker->getUserIds());
        }
    }

    public function prePersist(Chapter $chapter, LifecycleEventArgs $event)
    {
        if (null !== $chapter->getText()) {
            $userPicker = new UserPickerContent($chapter->getText());
            $chapter->setUserPicker($userPicker);
            $chapter->setText($userPicker->getFinalText());
        }
    }
}
