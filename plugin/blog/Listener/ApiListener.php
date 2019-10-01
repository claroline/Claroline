<?php

namespace Icap\BlogBundle\Listener;

use Claroline\CoreBundle\Event\User\MergeUsersEvent;
use Icap\BlogBundle\Manager\BlogManager;
use Icap\BlogBundle\Manager\CommentManager;
use Icap\BlogBundle\Manager\PostManager;

/**
 * Class ApiListener.
 */
class ApiListener
{
    /** @var CommentManager */
    private $commentManager;

    /** @var PostManager */
    private $postManager;

    /** @var BlogManager */
    private $blogManager;

    /**
     * @param CommentManager $commentManager
     * @param PostManager    $postManager
     * @param BlogManager    $blogManager
     */
    public function __construct(CommentManager $commentManager, PostManager $postManager, BlogManager $blogManager)
    {
        $this->commentManager = $commentManager;
        $this->postManager = $postManager;
        $this->blogManager = $blogManager;
    }

    /**
     * @param MergeUsersEvent $event
     */
    public function onMerge(MergeUsersEvent $event)
    {
        // Replace user of Comment nodes
        $commentCount = $this->commentManager->replaceCommentAuthor($event->getRemoved(), $event->getKept());
        $event->addMessage("[IcapBlogBundle] updated Comment count: $commentCount");

        // Replace user of Post nodes
        $postCount = $this->postManager->replacePostAuthor($event->getRemoved(), $event->getKept());
        $event->addMessage("[IcapBlogBundle] updated Post count: $postCount");

        // Replace user of Blog members
        $this->blogManager->replaceMemberAuthor($event->getRemoved(), $event->getKept());
    }
}
