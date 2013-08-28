<?php

namespace ICAP\BlogBundle\Controller;

use Claroline\CoreBundle\Event\Log\LogResourceChildUpdateEvent;
use Claroline\CoreBundle\Event\Log\LogResourceReadEvent;
use Claroline\CoreBundle\Event\Log\LogResourceUpdateEvent;
use ICAP\BlogBundle\Entity\Blog;
use Claroline\CoreBundle\Library\Resource\ResourceCollection;
use ICAP\BlogBundle\Entity\Post;
use Symfony\Bundle\FrameworkBundle\Controller\Controller as BaseController;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class Controller extends BaseController
{
    const BLOG_TYPE         = 'icap_blog';
    const BLOG_POST_TYPE    = 'icap_blog_post';
    const BLOG_COMMENT_TYPE = 'icap_blog_comment';

    /**
     * @param string $permission
     *
     * @param Blog $blog
     *
     * @throws AccessDeniedException
     */
    protected function checkAccess($permission, Blog $blog)
    {
        $collection = new ResourceCollection(array($blog));
        if (!$this->get('security.context')->isGranted($permission, $collection)) {
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
        if ($this->get('security.context')->isGranted($permission, new ResourceCollection(array($blog)))) {
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
        $postDatas          = $this->get('icap.blog.post_repository')->findArchiveDatasByBlog($blog);
        $archiveDatas = array();

        $translator = $this->get('translator');

        foreach ($postDatas as $postData) {
            $archiveDatas[$postData['year']][] = array(
                'year'  => $postData['year'],
                'month' => $translator->trans('month.' . date("F", mktime(0, 0, 0, $postData['month'], 10)), array(), 'platform'),
                'count' => $postData['number']
            );
        }

        return $archiveDatas;
    }

    /***
     * @param Blog   $blog
     * @param string $childType
     * @param string $action
     * @param array  $details
     * @return Controller
     */
    protected function dispatchChildEvent(Blog $blog, $childType, $action, $details = array())
    {
        $log = new LogResourceChildUpdateEvent(
            $blog->getResourceNode(),
            $childType,
            $action,
            $details
        );

        $this->get('event_dispatcher')->dispatch('log', $log);

        return $this;
    }

    /**
     * @param Blog $blog
     *
     * @return Controller
     */
    protected function dispatchBlogConfigureEvent(Blog $blog)
    {
        return $this->dispatchChildEvent($blog, self::BLOG_TYPE, LogResourceChildUpdateEvent::CHILD_ACTION_UPDATE);
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
        $this->get('event_dispatcher')->dispatch('log', $logEvent);

        return $this;
    }

    /**
     * @param Blog                         $blog
     *
     * @param \ICAP\BlogBundle\Entity\Post $post
     *
     * @return Controller
     */
    protected function dispatchPostCreateEvent(Blog $blog, Post $post)
    {
        $details = array(
            'icap_blog_post' => array(
                'id'    => $post->getId(),
                'title' => $post->getTitle()
            )
        );

        return $this->dispatchChildEvent($blog, self::BLOG_POST_TYPE, LogResourceChildUpdateEvent::CHILD_ACTION_CREATE, $details);
    }

    /**
     * @param Blog $blog
     *
     * @param Post $post
     *
     * @return Controller
     */
    protected function dispatchPostReadEvent(Blog $blog, Post $post)
    {
        $details = array(
            'icap_blog_post' => array(
                'id'    => $post->getId(),
                'title' => $post->getTitle()
            )
        );

        return $this->dispatchChildEvent($blog, self::BLOG_POST_TYPE, LogResourceChildUpdateEvent::CHILD_ACTION_READ, $details);
    }

    /**
     * @param Blog                         $blog
     *
     * @param \ICAP\BlogBundle\Entity\Post $post
     *
     * @return Controller
     */
    protected function dispatchPostUpdateEvent(Blog $blog, Post $post)
    {
        $details = array(
            'icap_blog_post' => array(
                'id'    => $post->getId(),
                'title' => $post->getTitle()
            )
        );

        return $this->dispatchChildEvent($blog, self::BLOG_POST_TYPE, LogResourceChildUpdateEvent::CHILD_ACTION_UPDATE, $details);
    }

    /**
     * @param Blog                         $blog
     *
     * @param \ICAP\BlogBundle\Entity\Post $post
     *
     * @return Controller
     */
    protected function dispatchPostDeleteEvent(Blog $blog, Post $post)
    {
        $details = array(
            'icap_blog_post' => array(
                'id'    => $post->getId(),
                'title' => $post->getTitle()
            )
        );

        return $this->dispatchChildEvent($blog, self::BLOG_POST_TYPE, LogResourceChildUpdateEvent::CHILD_ACTION_DELETE, $details);
    }

    /**
     * @param Blog $blog
     *
     * @param Post $post
     *
     * @return Controller
     */
    protected function dispatchCommentCreateEvent(Blog $blog, Post $post)
    {
        $details = array(
            'icap_blog_post' => array(
                'id'    => $post->getId(),
                'title' => $post->getTitle()
            )
        );

        return $this->dispatchChildEvent($blog, self::BLOG_COMMENT_TYPE, LogResourceChildUpdateEvent::CHILD_ACTION_CREATE, $details);
    }


    /**
     * @param Blog $blog
     *
     * @param Post $post
     *
     * @return Controller
     */
    protected function dispatchCommentDeleteEvent(Blog $blog, Post $post)
    {
        $details = array(
            'icap_blog_post' => array(
                'id'    => $post->getId(),
                'title' => $post->getTitle()
            )
        );

        return $this->dispatchChildEvent($blog, self::BLOG_COMMENT_TYPE, LogResourceChildUpdateEvent::CHILD_ACTION_DELETE, $details);
    }
}
