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
use JMS\DiExtraBundle\Annotation as DI;
use Icap\NotificationBundle\Manager\NotificationManager as NotificationManager;

/**
 * @DI\Service("icap.lesson_bundle.entity_listener.chapter")
 * @DI\Tag("doctrine.entity_listener")
 */
class ChapterListener
{
    /** @var  \Icap\NotificationBundle\Manager\NotificationManager */
    private $notificationManager;

    /**
     * @DI\InjectParams({
     * "notificationManager" = @DI\Inject("icap.notification.manager"),
     * })
     */
    public function __construct(NotificationManager $notificationManager)
    {
        $this->notificationManager = $notificationManager;
    }

    public function postPersist(Chapter $chapter, LifecycleEventArgs $event)
    {
        $userPicker = $chapter->getUserPicker();
        $lesson = $chapter->getLesson();
        if (
            $userPicker !== null &&
            count($userPicker->getUserIds()) > 0 &&
            $lesson->getResourceNode() !== null
        ) {
            $details = array(
                'chapter' => array(
                    'lesson' => $lesson->getId(),
                    'chapter' => $chapter->getId(),
                    'title' => $chapter->getTitle(),
                ),
                'resource' => array(
                    'id' => $lesson->getId(),
                    'name' => $lesson->getResourceNode()->getName(),
                    'type' => $lesson->getResourceNode()->getResourceType()->getName(),
                ),
            );
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
        if ($chapter->getText() != null) {
            $userPicker = new UserPickerContent($chapter->getText());
            $chapter->setUserPicker($userPicker);
            $chapter->setText($userPicker->getFinalText());
        }
    }
}
