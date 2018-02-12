<?php

namespace Icap\BlogBundle\Controller;

use Claroline\CoreBundle\Entity\Resource\AbstractResourceEvaluation;
use Claroline\CoreBundle\Entity\Resource\ResourceNode;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Event\Log\LogResourceReadEvent;
use Claroline\CoreBundle\Event\Log\LogResourceUpdateEvent;
use Claroline\CoreBundle\Library\Resource\ResourceCollection;
use FOS\RestBundle\Controller\FOSRestController;
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
use Icap\BlogBundle\Form\BlogBannerType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class BaseController extends FOSRestController
{
    const BLOG_TYPE = 'icap_blog';
    const BLOG_POST_TYPE = 'icap_blog_post';
    const BLOG_COMMENT_TYPE = 'icap_blog_comment';

    /**
     * @param string $permissions
     * @param Blog   $blog
     * @param string $comparison
     *
     * @throws \Symfony\Component\Security\Core\Exception\AccessDeniedException
     */
    protected function checkAccess($permissions, Blog $blog, $comparison = 'AND')
    {
        $isGranted = true;

        if (false === is_array($permissions)) {
            $permissions = [$permissions];
        }

        foreach ($permissions as $permission) {
            $currentIsGranted = $this->get('security.authorization_checker')->isGranted($permission, $blog);
            if ('OR' === $comparison) {
                $isGranted = $isGranted || $currentIsGranted;
            } else {
                $isGranted = $isGranted && $currentIsGranted;
            }
        }

        if (false === $isGranted) {
            throw new AccessDeniedException();
        }

        $logEvent = new LogResourceReadEvent($blog->getResourceNode());
        $this->get('event_dispatcher')->dispatch('log', $logEvent);
    }

    public function orderPanelsAction(Blog $blog)
    {
        return $this->render(
            'IcapBlogBundle::aside.html.twig',
            [
                'orderPanelInfos' => $this->orderPanels($blog),
                'blog' => $blog,
                'archives' => $this->getArchiveDatas($blog),
            ]
        );
    }

    protected function orderPanels(Blog $blog)
    {
        $panelInfo = $this->get('icap_blog.manager.blog')->getPanelInfos();
        $mask = $blog->getOptions()->getListWidgetBlog();
        $orderPanelsTable = [];

        for ($maskPosition = 0, $entreTableau = 0; $maskPosition < strlen($mask); $maskPosition += 2, $entreTableau++) {
            $orderPanelsTable[] = [
                'nameTemplate' => $panelInfo[$mask[$maskPosition]],
                'visibility' => (int) $mask[$maskPosition + 1],
                'id' => (int) $mask[$maskPosition],
            ];
        }

        return $orderPanelsTable;
    }

    /**
     * @param string $permission
     * @param Blog   $blog
     *
     * @return bool
     */
    protected function isUserGranted($permission, Blog $blog)
    {
        $checkPermission = false;
        if ($this->get('security.authorization_checker')->isGranted($permission, new ResourceCollection([$blog->getResourceNode()]))) {
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
        $postDatas = $this->get('icap.blog.post_repository')->findArchiveDatasByBlog($blog);
        $archiveDatas = [];

        $translator = $this->get('translator');

        foreach ($postDatas as $postData) {
            $publicationDate = $postData->getPublicationDate();
            $year = $publicationDate->format('Y');
            $month = $publicationDate->format('m');

            if (!isset($archiveDatas[$year][$month])) {
                $archiveDatas[$year][$month] = [
                    'year' => $year,
                    'month' => $translator->trans('month.'.date('F', mktime(0, 0, 0, $month, 10)), [], 'platform'),
                    'count' => 1,
                    'urlParameters' => $year.'/'.$month,
                ];
            } else {
                ++$archiveDatas[$year][$month]['count'];
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
     * @param Post $post
     *
     * @return Controller
     */
    protected function dispatchPostPublishEvent(Post $post)
    {
        $event = new LogPostPublishEvent($post);

        return $this->dispatch($event);
    }

    /**
     * @param Post    $post
     * @param Comment $comment
     *
     * @return Controller
     */
    protected function dispatchCommentCreateEvent(Post $post, Comment $comment)
    {
        $event = new LogCommentCreateEvent($post, $comment, $this->get('translator'));

        return $this->dispatch($event);
    }

    /**
     * @param Post    $post
     * @param Comment $comment
     *
     * @return Controller
     */
    protected function dispatchCommentDeleteEvent(Post $post, Comment $comment)
    {
        $event = new LogCommentDeleteEvent($post, $comment);

        return $this->dispatch($event);
    }

    /**
     * @param Post    $post
     * @param Comment $comment
     * @param $changeSet
     *
     * @return Controller
     */
    protected function dispatchCommentUpdateEvent(Post $post, Comment $comment, $changeSet)
    {
        $event = new LogCommentUpdateEvent($post, $comment, $changeSet, $this->get('translator'));

        return $this->dispatch($event);
    }

    /**
     * @param Post    $post
     * @param Comment $comment
     *
     * @return Controller
     */
    protected function dispatchCommentPublishEvent(Post $post, Comment $comment)
    {
        $event = new LogCommentPublishEvent($post, $comment, $this->get('translator'));

        return $this->dispatch($event);
    }

    protected function checkPermission($permission, Blog $blog = null)
    {
        return $this->get('security.authorization_checker')->isGranted($permission, $blog);
    }

    /**
     * Logs participation in resource tracking.
     *
     * @param ResourceNode $node
     * @param User         $user
     * @param \DateTime    $date
     */
    protected function updateResourceTracking(ResourceNode $node, User $user, \DateTime $date)
    {
        $this->get('claroline.manager.resource_evaluation_manager')->updateResourceUserEvaluationData(
            $node,
            $user,
            $date,
            AbstractResourceEvaluation::STATUS_PARTICIPATED
        );
    }
}
