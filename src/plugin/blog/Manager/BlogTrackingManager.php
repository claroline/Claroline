<?php

namespace Icap\BlogBundle\Manager;

use Claroline\CoreBundle\Entity\Resource\ResourceNode;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Event\Log\LogResourceUpdateEvent;
use Claroline\CoreBundle\Manager\Resource\ResourceEvaluationManager;
use Claroline\EvaluationBundle\Entity\Evaluation\AbstractEvaluation;
use Icap\BlogBundle\Entity\Blog;
use Icap\BlogBundle\Entity\BlogOptions;
use Icap\BlogBundle\Entity\Comment;
use Icap\BlogBundle\Entity\Post;
use Icap\BlogBundle\Event\Log\LogBlogConfigureBannerEvent;
use Icap\BlogBundle\Event\Log\LogBlogConfigureEvent;
use Icap\BlogBundle\Event\Log\LogCommentCreateEvent;
use Icap\BlogBundle\Event\Log\LogCommentDeleteEvent;
use Icap\BlogBundle\Event\Log\LogCommentPublishEvent;
use Icap\BlogBundle\Event\Log\LogCommentUpdateEvent;
use Icap\BlogBundle\Event\Log\LogPostCreateEvent;
use Icap\BlogBundle\Event\Log\LogPostDeleteEvent;
use Icap\BlogBundle\Event\Log\LogPostPublishEvent;
use Icap\BlogBundle\Event\Log\LogPostReadEvent;
use Icap\BlogBundle\Event\Log\LogPostUpdateEvent;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Translation\TranslatorInterface;

class BlogTrackingManager
{
    const BLOG_TYPE = 'icap_blog';
    const BLOG_POST_TYPE = 'icap_blog_post';
    const BLOG_COMMENT_TYPE = 'icap_blog_comment';

    private $eventDispatcher;
    private $evalutionManager;
    private $translator;

    /**
     * Constructor.
     */
    public function __construct(
        EventDispatcherInterface $eventDispatcher,
        ResourceEvaluationManager $evalutionManager,
        TranslatorInterface $translator)
    {
        $this->eventDispatcher = $eventDispatcher;
        $this->evalutionManager = $evalutionManager;
        $this->translator = $translator;
    }

    public function dispatch($event)
    {
        $this->eventDispatcher->dispatch($event, 'log');

        return $this;
    }

    /**
     * @param array $changeSet
     *
     * @return Controller
     */
    public function dispatchBlogUpdateEvent(Blog $blog, $changeSet)
    {
        $logEvent = new LogResourceUpdateEvent($blog->getResourceNode(), $changeSet);

        return $this->dispatch($logEvent);
    }

    /**
     * @param array $changeSet
     *
     * @return Controller
     */
    public function dispatchBlogConfigureEvent(BlogOptions $blogOptions, $changeSet)
    {
        $event = new LogBlogConfigureEvent($blogOptions, $changeSet);

        return $this->dispatch($event);
    }

    /**
     * @param array $changeSet
     *
     * @return Controller
     */
    public function dispatchBlogConfigureBannerEvent(BlogOptions $blogOptions, $changeSet)
    {
        $event = new LogBlogConfigureBannerEvent($blogOptions, $changeSet);

        return $this->dispatch($event);
    }

    /**
     * @return Controller
     */
    public function dispatchPostCreateEvent(Blog $blog, Post $post)
    {
        $event = new LogPostCreateEvent($post);

        return $this->dispatch($event);
    }

    /**
     * @return Controller
     */
    public function dispatchPostReadEvent(Post $post)
    {
        $event = new LogPostReadEvent($post);

        return $this->dispatch($event);
    }

    /**
     * @param array $changeSet
     *
     * @return Controller
     */
    public function dispatchPostUpdateEvent(Post $post, $changeSet)
    {
        $event = new LogPostUpdateEvent($post, $changeSet);

        return $this->dispatch($event);
    }

    /**
     * @return Controller
     */
    public function dispatchPostDeleteEvent(Post $post)
    {
        $event = new LogPostDeleteEvent($post);

        return $this->dispatch($event);
    }

    /**
     * @return Controller
     */
    public function dispatchPostPublishEvent(Post $post)
    {
        $event = new LogPostPublishEvent($post);

        return $this->dispatch($event);
    }

    /**
     * @return Controller
     */
    public function dispatchCommentCreateEvent(Post $post, Comment $comment)
    {
        $event = new LogCommentCreateEvent($post, $comment, $this->translator);

        return $this->dispatch($event);
    }

    /**
     * @return Controller
     */
    public function dispatchCommentDeleteEvent(Post $post, Comment $comment)
    {
        $event = new LogCommentDeleteEvent($post, $comment);

        return $this->dispatch($event);
    }

    /**
     * @param $changeSet
     *
     * @return Controller
     */
    public function dispatchCommentUpdateEvent(Post $post, Comment $comment, $changeSet)
    {
        $event = new LogCommentUpdateEvent($post, $comment, $changeSet, $this->translator);

        return $this->dispatch($event);
    }

    /**
     * @return Controller
     */
    public function dispatchCommentPublishEvent(Post $post, Comment $comment)
    {
        $event = new LogCommentPublishEvent($post, $comment, $this->translator);

        return $this->dispatch($event);
    }

    /**
     * Logs participation in resource tracking.
     */
    public function updateResourceTracking(ResourceNode $node, User $user, \DateTime $date)
    {
        $this->evalutionManager->updateResourceUserEvaluationData(
            $node,
            $user,
            $date,
            ['status' => AbstractEvaluation::STATUS_PARTICIPATED]
        );
    }
}
