<?php

namespace Icap\BlogBundle\Controller;

use Icap\BlogBundle\Entity\Comment;
use Icap\BlogBundle\Entity\Post;
use Icap\BlogBundle\Entity\Blog;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;

class CommentController extends Controller
{
    /**
     * @Route("/{blogId}/{postSlug}/comment/delete/{commentId}", name="icap_blog_comment_delete", requirements={"blogId" = "\d+"})
     *
     * @ParamConverter("blog", class="IcapBlogBundle:Blog", options={"id" = "blogId"})
     * @ParamConverter("post", class="IcapBlogBundle:Post", options={"mapping": {"postSlug": "slug"}})
     * @ParamConverter("comment", class="IcapBlogBundle:Comment", options={"id" = "commentId"})
     * @Template()
     */
    public function deleteAction(Blog $blog, Post $post, Comment $comment)
    {
        $this->checkAccess("EDIT", $blog);

        $entityManager = $this->getDoctrine()->getManager();
        $translator    = $this->get('translator');
        $flashBag      = $this->get('session')->getFlashBag();

        try {
            $entityManager->remove($comment);
            $entityManager->flush();

            $this->dispatchCommentDeleteEvent($blog, $post, $comment);

            $flashBag->add('success', $translator->trans('icap_blog_comment_delete_success', array(), 'icap_blog'));
        } catch (\Exception $exception) {
            $flashBag->add('error', $translator->trans('icap_blog_comment_delete_error', array(), 'icap_blog'));
        }

        return $this->redirect($this->generateUrl('icap_blog_post_view', array('blogId' => $blog->getId(), 'postSlug' => $post->getSlug())));
    }

    /**
     * @Route("/{blogId}/{postSlug}/comment/publish/{commentId}", name="icap_blog_comment_publish", requirements={"blogId" = "\d+"})
     *
     * @ParamConverter("blog", class="IcapBlogBundle:Blog", options={"id" = "blogId"})
     * @ParamConverter("post", class="IcapBlogBundle:Post", options={"mapping": {"postSlug": "slug"}})
     * @ParamConverter("comment", class="IcapBlogBundle:Comment", options={"id" = "commentId"})
     * @Template()
     */
    public function publishAction(Blog $blog, Post $post, Comment $comment)
    {
        $comment->publish();

        $translator = $this->get('translator');

        $messages   = array(
            'success' => $translator->trans('icap_blog_comment_publish_success', array(), 'icap_blog'),
            'error'   => $translator->trans('icap_blog_comment_publish_error', array(), 'icap_blog')
        );

        return $this->changePublishStatus($blog, $post, $comment, $messages);
    }

    /**
     * @Route("/{blogId}/{postSlug}/comment/unpublish/{commentId}", name="icap_blog_comment_unpublish", requirements={"blogId" = "\d+"})
     *
     * @ParamConverter("blog", class="IcapBlogBundle:Blog", options={"id" = "blogId"})
     * @ParamConverter("post", class="IcapBlogBundle:Post", options={"mapping": {"postSlug": "slug"}})
     * @ParamConverter("comment", class="IcapBlogBundle:Comment", options={"id" = "commentId"})
     * @Template()
     */
    public function unpublishAction(Blog $blog, Post $post, Comment $comment)
    {
        $comment->unpublish();

        $translator = $this->get('translator');

        $messages   = array(
            'success' => $translator->trans('icap_blog_comment_unpublish_success', array(), 'icap_blog'),
            'error'   => $translator->trans('icap_blog_comment_unpublish_error', array(), 'icap_blog')
        );

        return $this->changePublishStatus($blog, $post, $comment, $messages);
    }

    /**
     * @param Blog    $blog
     * @param Post    $post
     * @param Comment $comment
     * @param array   $messages
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    protected function changePublishStatus(Blog $blog, Post $post, Comment $comment, array $messages)
    {
        $this->checkAccess("EDIT", $blog);

        $entityManager = $this->getDoctrine()->getManager();
        $flashBag      = $this->get('session')->getFlashBag();

        try {
            $entityManager->persist($comment);
            $entityManager->flush();

            $flashBag->add('success', $messages['success']);
        } catch (\Exception $exception) {
            $flashBag->add('error', $messages['error']);
        }

        return $this->redirect($this->generateUrl('icap_blog_post_view', array('blogId' => $blog->getId(), 'postSlug' => $post->getSlug())));
    }
}
