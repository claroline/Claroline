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

use Doctrine\ORM\Event\LifecycleEventArgs;
use Icap\BlogBundle\Entity\Post;
use Icap\NotificationBundle\Entity\UserPickerContent;
use JMS\DiExtraBundle\Annotation as DI;
use Icap\NotificationBundle\Manager\NotificationManager as NotificationManager;

/**
 * @DI\Service("icap.blog_bundle.entity_listener.post")
 * @DI\Tag("doctrine.entity_listener")
 */
class PostListener
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

    public function postPersist(Post $post, LifecycleEventArgs $event)
    {
        $userPicker = $post->getUserPicker();
        $blog = $post->getBlog();
        if (
            $post->isPublished() &&
            $userPicker !== null &&
            count($userPicker->getUserIds()) > 0 &&
            $blog->getResourceNode() !== null
        ) {
            $details = array(
                'post' => array(
                    'blog' => $blog->getId(),
                    'title' => $post->getTitle(),
                    'slug' => $post->getSlug(),
                    'published' => $post->isPublished(),
                    'author' => $post->getAuthor()->getFirstName().' '.$post->getAuthor()->getLastName(),
                    'authorId' => $post->getAuthor()->getId(),
                ),
                'resource' => array(
                    'id' => $blog->getId(),
                    'name' => $blog->getResourceNode()->getName(),
                    'type' => $blog->getResourceNode()->getResourceType()->getName(),
                ),
            );
            $notification = $this->notificationManager->createNotification(
                'resource-icap_blog-post-user_tagged',
                'blog',
                $blog->getResourceNode()->getId(),
                $details
            );
            $this->notificationManager->notifyUsers($notification, $userPicker->getUserIds());
        }
    }

    public function prePersist(Post $post, LifecycleEventArgs $event)
    {
        if ($post->getContent() != null) {
            $userPicker = new UserPickerContent($post->getContent());
            $post->setUserPicker($userPicker);
            $post->setContent($userPicker->getFinalText());
        }
    }

    public function preUpdate(Post $post, LifecycleEventArgs $event)
    {
        $this->prePersist($post, $event);
    }

    public function postUpdate(Post $post, LifecycleEventArgs $event)
    {
        $this->postPersist($post, $event);
    }
}
