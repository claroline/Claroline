<?php

namespace ICAP\BlogBundle\Controller;

use ICAP\BlogBundle\Entity\Post;
use ICAP\BlogBundle\Entity\Blog;
use ICAP\BlogBundle\Form\PostType;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\HttpFoundation\Request;

class PostController extends Controller
{
    /**
     * @Route("/{blogId}/post/new", name="icap_blog_post_new", requirements={"blogId" = "\d+"})
     *
     * @ParamConverter("blog", class="ICAPBlogBundle:Blog", options={"id" = "blogId"})
     * @Template()
     */
    public function newAction(Request $request, Blog $blog)
    {
        $this->checkAccess("EDIT", $blog);

        $post = new Post();
        $post
            ->setBlog($blog)
            ->setAuthor($this->getUser())
        ;

        $translator = $this->get('translator');

        $messages = array(
            'success' => $translator->trans('icap_blog_post_add_success', array(), 'icap_blog'),
            'error'   =>$translator->trans('icap_blog_post_add_error', array(), 'icap_blog')
        );

        return $this->persistPost($request, $blog, $post, $messages);
    }

    /**
     * @Route("/{blogId}/post/edit/{postSlug}", name="icap_blog_post_edit", requirements={"blogId" = "\d+"})
     *
     * @ParamConverter("blog", class="ICAPBlogBundle:Blog", options={"id" = "blogId"})
     * @ParamConverter("post", class="ICAPBlogBundle:Post", options={"slug" = "postSlug"})
     * @Template()
     */
    public function editAction(Request $request, Blog $blog, Post $post)
    {
        $this->checkAccess("EDIT", $blog);

        $translator = $this->get('translator');

        $messages = array(
            'success' => $translator->trans('icap_blog_post_edit_success', array(), 'icap_blog'),
            'error'   =>$translator->trans('icap_blog_post_edit_error', array(), 'icap_blog')
        );

        return $this->persistPost($request, $blog, $post, $messages);
    }

    protected function persistPost(Request $request, Blog $blog, Post $post, array $messages)
    {
        $form = $this->createForm(new PostType(), $post);

        if("POST" === $request->getMethod()) {
            $form->handleRequest($request);
            if ($form->isValid()) {
                $flashBag = $this->get('session')->getFlashBag();
                $entityManager = $this->getDoctrine()->getManager();

                try {
                    $entityManager->persist($post);
                    $entityManager->flush();

                    $flashBag->add('success', $messages['success']);
                }
                catch(\Exception $exception)
                {
                    $flashBag->add('error', $messages['error']);
                }
                return $this->redirect($this->generateUrl('icap_blog_view', array('blogId' => $blog->getId())));
            }
        }

        return array(
            '_resource' => $blog,
            'post'      => $post,
            'form'      => $form->createView()
        );
    }

    /**
     * @Route("/{blogId}/post/delete/{postSlug}", name="icap_blog_post_delete", requirements={"blogId" = "\d+"})
     *
     * @ParamConverter("blog", class="ICAPBlogBundle:Blog", options={"id" = "blogId"})
     * @ParamConverter("post", class="ICAPBlogBundle:Post", options={"slug" = "postSlug"})
     * @Template()
     */
    public function deleteAction(Blog $blog, Post $post)
    {
        $this->checkAccess("EDIT", $blog);

        $entityManager = $this->getDoctrine()->getManager();
        $translator    = $this->get('translator');
        $flashBag      = $this->get('session')->getFlashBag();

        try {
            $entityManager->remove($post);
            $entityManager->flush();

            $flashBag->add('success', $translator->trans('icap_blog_post_delete_success', array(), 'icap_blog'));
        }
        catch(\Exception $exception)
        {
            $flashBag->add('error', $translator->trans('icap_blog_post_delete_error', array(), 'icap_blog'));
        }

        return $this->redirect($this->generateUrl('icap_blog_view', array('blogId' => $blog->getId())));
    }

    /**
     * @Route("/{blogId}/post/publish/{postSlug}", name="icap_blog_post_publish", requirements={"blogId" = "\d+"})
     *
     * @ParamConverter("blog", class="ICAPBlogBundle:Blog", options={"id" = "blogId"})
     * @ParamConverter("post", class="ICAPBlogBundle:Post", options={"slug" = "postSlug"})
     * @Template()
     */
    public function publishAction(Blog $blog, Post $post)
    {
        $post->publish();

        $translator = $this->get('translator');

        $messages   = array(
            'success' => $translator->trans('icap_blog_post_publish_success', array(), 'icap_blog'),
            'error'   => $translator->trans('icap_blog_post_publish_error', array(), 'icap_blog')
        );

        return $this->changePublishStatus($blog, $post, $messages);
    }

    /**
     * @Route("/{blogId}/post/unpublish/{postSlug}", name="icap_blog_post_unpublish", requirements={"blogId" = "\d+"})
     *
     * @ParamConverter("blog", class="ICAPBlogBundle:Blog", options={"id" = "blogId"})
     * @ParamConverter("post", class="ICAPBlogBundle:Post", options={"slug" = "postSlug"})
     * @Template()
     */
    public function unpublishAction(Blog $blog, Post $post)
    {
        $post->unpublish();

        $translator = $this->get('translator');

        $messages   = array(
            'success' => $translator->trans('icap_blog_post_unpublish_success', array(), 'icap_blog'),
            'error'   => $translator->trans('icap_blog_post_unpublish_error', array(), 'icap_blog')
        );

        return $this->changePublishStatus($blog, $post, $messages);
    }

    /**
     * @param Blog  $blog
     * @param Post  $post
     * @param array $messages
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    protected function changePublishStatus(Blog $blog, Post $post, array $messages)
    {
        $this->checkAccess("EDIT", $blog);

        $entityManager = $this->getDoctrine()->getManager();
        $flashBag      = $this->get('session')->getFlashBag();

        try {
            $entityManager->persist($post);
            $entityManager->flush();

            $flashBag->add('success', $messages['success']);
        }
        catch(\Exception $exception)
        {
            $flashBag->add('error', $messages['error']);
        }

        return $this->redirect($this->generateUrl('icap_blog_view', array('blogId' => $blog->getId())));
    }
}
