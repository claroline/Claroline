<?php

/*
* This file is part of the Claroline Connect package.
*
* (c) Claroline Consortium <consortium@claroline.net>
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*/

namespace Icap\BlogBundle\Listener;

use Icap\BlogBundle\Entity\Post;
use Icap\NotificationBundle\Entity\UserPickerContent;
use Icap\NotificationBundle\Manager\NotificationManager;

/**
 * TODO : listen to crud events instead.
 */
class PostListener
{
    /** @var NotificationManager */
    private $notificationManager;

    public function __construct(NotificationManager $notificationManager)
    {
        $this->notificationManager = $notificationManager;
    }

    public function postPersist(Post $post)
    {
        $userPicker = $post->getUserPicker();
        $blog = $post->getBlog();
        if (
            $post->isPublished() &&
            null !== $userPicker &&
            count($userPicker->getUserIds()) > 0 &&
            null !== $blog->getResourceNode()
        ) {
            $details = [
                'post' => [
                    'blog' => $blog->getId(),
                    'title' => $post->getTitle(),
                    'slug' => $post->getSlug(),
                    'published' => $post->isPublished(),
                    'author' => $post->getCreator()->getFirstName().' '.$post->getCreator()->getLastName(),
                    'authorId' => $post->getCreator()->getId(),
                ],
                'resource' => [
                    'id' => $blog->getId(),
                    'name' => $blog->getResourceNode()->getName(),
                    'type' => $blog->getResourceNode()->getResourceType()->getName(),
                ],
            ];
            $notification = $this->notificationManager->createNotification(
                'resource-icap_blog-post-user_tagged',
                'blog',
                $blog->getResourceNode()->getId(),
                $details
            );
            $this->notificationManager->notifyUsers($notification, $userPicker->getUserIds());
        }
    }

    public function prePersist(Post $post)
    {
        if (null !== $post->getContent()) {
            $userPicker = new UserPickerContent($post->getContent());
            $post->setUserPicker($userPicker);
            $post->setContent($userPicker->getFinalText());
        }
    }

    public function preUpdate(Post $post)
    {
        $this->prePersist($post);
    }

    public function postUpdate(Post $post)
    {
        $this->postPersist($post);
    }
}
