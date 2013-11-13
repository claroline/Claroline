<?php

namespace Icap\BlogBundle\Controller;

use Claroline\CoreBundle\Event\Log\LogResourceChildUpdateEvent;
use Claroline\CoreBundle\Event\Log\LogResourceReadEvent;
use Claroline\CoreBundle\Event\Log\LogResourceUpdateEvent;
use Icap\BlogBundle\Entity\Blog;
use Claroline\CoreBundle\Library\Resource\ResourceCollection;
use Icap\BlogBundle\Entity\BlogOptions;
use Icap\BlogBundle\Entity\Comment;
use Icap\BlogBundle\Entity\Post;
use Icap\BlogBundle\Event\Log\LogBlogConfigureBannerEvent;
use Icap\BlogBundle\Event\Log\LogBlogConfigureEvent;
use Icap\BlogBundle\Event\Log\LogCommentCreateEvent;
use Icap\BlogBundle\Event\Log\LogCommentDeleteEvent;
use Icap\BlogBundle\Event\Log\LogPostCreateEvent;
use Icap\BlogBundle\Event\Log\LogPostDeleteEvent;
use Icap\BlogBundle\Event\Log\LogPostReadEvent;
use Icap\BlogBundle\Event\Log\LogPostUpdateEvent;
use Icap\BlogBundle\Form\BlogBannerType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller as BaseController;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class Controller extends BaseController
{
    const BLOG_TYPE         = 'icap_blog';
    const BLOG_POST_TYPE    = 'icap_blog_post';
    const BLOG_COMMENT_TYPE = 'icap_blog_comment';

    /**
     * @param string $permissions
     *
     * @param Blog   $blog
     *
     * @param string $comparison
     *
     * @throws \Symfony\Component\Security\Core\Exception\AccessDeniedException
     */
    protected function checkAccess($permissions, Blog $blog, $comparison = "AND")
    {
        $collection = new ResourceCollection(array($blog->getResourceNode()));
        $isGranted = true;

        if (false === is_array($permissions)) {
            $permissions = array($permissions);
        }

        foreach ($permissions as $permission) {
            if ("OR" === $comparison) {
                $isGranted = $isGranted || $this->get('security.context')->isGranted($permission, $collection);
            }
            else {
                $isGranted += $this->get('security.context')->isGranted($permission, $collection);
            }
        }

        if (false === $isGranted) {
            throw new AccessDeniedException($collection->getErrorsForDisplay());
        }

        $logEvent = new LogResourceReadEvent($blog->getResourceNode());
        $this->get('event_dispatcher')->dispatch('log', $logEvent);
    }
    /**
     * @param string $permission
     *
     * @param Blog $blog
     *
     * @return bool
     */
    protected function isUserGranted($permission, Blog $blog)
    {
        $checkPermission = false;
        if ($this->get('security.context')->isGranted($permission, new ResourceCollection(array($blog->getResourceNode())))) {
            $checkPermission = true;
        }

        return $checkPermission;
    }

    /**
     * @param Blog $blog
     *
     * @return array
     */
    protected function getArchiveDatas(Blog $blog)
    {
        $postDatas    = $this->get('icap.blog.post_repository')->findArchiveDatasByBlog($blog);
        $archiveDatas = array();

        $translator = $this->get('translator');

        foreach ($postDatas as $postData) {
            $publicationDate = $postData->getPublicationDate();
            $year = $publicationDate->format('Y');
            $month = $publicationDate->format('m');

            if (!isset($archiveDatas[$year][$month])) {
                $archiveDatas[$year][$month] = array(
                    'year'          => $year,
                    'month'         => $translator->trans('month.' . date("F", mktime(0, 0, 0, $month, 10)), array(), 'platform'),
                    'count'         => 1,
                    'urlParameters' => $month . '-' . $year
                );
            } else {
                $archiveDatas[$year][$month]['count']++;
            }
        }

        return $archiveDatas;
    }

    /**
     * @param BlogOptions $blogOptions
     *
     * @return \Symfony\Component\Form\Form
     */
    protected function getBannerForm(BlogOptions $blogOptions)
    {
        return $this->createForm(new BlogBannerType(), $blogOptions)->createView();
    }

    protected function dispatch($event)
    {
        $this->get('event_dispatcher')->dispatch('log', $event);

        return $this;
    }

    /**
     * @param Blog  $blog
     *
     * @param array $changeSet
     *
     * @return Controller
     */
    protected function dispatchBlogUpdateEvent(Blog $blog, $changeSet)
    {
        $logEvent = new LogResourceUpdateEvent($blog->getResourceNode(), $changeSet);

       return $this->dispatch($logEvent);
    }

    /**
     * @param BlogOptions $blogOptions
     *
     * @param array       $changeSet
     *
     * @return Controller
     */
    protected function dispatchBlogConfigureEvent(BlogOptions $blogOptions, $changeSet)
    {
        $event = new LogBlogConfigureEvent($blogOptions, $changeSet);

        return $this->dispatch($event);
    }

    /**
     * @param BlogOptions $blogOptions
     *
     * @param array       $changeSet
     *
     * @return Controller
     */
    protected function dispatchBlogConfigureBannerEvent(BlogOptions $blogOptions, $changeSet)
    {
        $event = new LogBlogConfigureBannerEvent($blogOptions, $changeSet);

        return $this->dispatch($event);
    }

    /**
     * @param Blog $blog
     *
     * @param Post $post
     *
     * @return Controller
     */
    protected function dispatchPostCreateEvent(Blog $blog, Post $post)
    {
        $event = new LogPostCreateEvent($post);

        return $this->dispatch($event);
    }

    /**
     * @param Post $post
     *
     * @return Controller
     */
    protected function dispatchPostReadEvent(Post $post)
    {
        $event = new LogPostReadEvent($post);

        return $this->dispatch($event);
    }

    /**
     * @param Post  $post
     *
     * @param array $changeSet
     *
     * @return Controller
     */
    protected function dispatchPostUpdateEvent(Post $post, $changeSet)
    {
        $event = new LogPostUpdateEvent($post, $changeSet);

        return $this->dispatch($event);
    }

    /**
     * @param Post $post
     *
     * @return Controller
     */
    protected function dispatchPostDeleteEvent(Post $post)
    {
        $event = new LogPostDeleteEvent($post);

        return $this->dispatch($event);
    }

    /**
     * @param Post    $post
     *
     * @param Comment $comment
     *
     * @return Controller
     */
    protected function dispatchCommentCreateEvent(Post $post, Comment $comment)
    {
        $event = new LogCommentCreateEvent($post, $comment);

        return $this->dispatch($event);
    }

    /**
     * @param Post    $post
     *
     * @param Comment $comment
     *
     * @return Controller
     */
    protected function dispatchCommentDeleteEvent(Post $post, Comment $comment)
    {
        $event = new LogCommentDeleteEvent($post, $comment);

        return $this->dispatch($event);
    }
}
