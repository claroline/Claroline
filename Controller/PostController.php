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

        $form = $this->createForm(new PostType(), $post);

        if("POST" === $request->getMethod()) {
            $form->handleRequest($request);
            if ($form->isValid()) {
                $translator = $this->get('translator');
                $flashBag = $this->get('session')->getFlashBag();
                $entityManager = $this->getDoctrine()->getManager();

                try {
                    $entityManager->persist($post);
                    $entityManager->flush();

                    $flashBag->add('success', $translator->trans('icap_blog_post_add_success', array(), 'icap_blog'));
                }
                catch(\Exception $exception)
                {
                    $flashBag->add('error', $translator->trans('icap_blog_post_add_error', array(), 'icap_blog'));
                }
                return $this->redirect($this->generateUrl('icap_blog_view', array('blogId' => $blog->getId())));
            }
        }

        return array(
            '_resource' => $blog,
            'form'      => $form->createView()
        );
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

        $form = $this->createForm(new PostType(), $post);

        if("POST" === $request->getMethod()) {
            $form->handleRequest($request);
            if ($form->isValid()) {
                $translator = $this->get('translator');
                $flashBag = $this->get('session')->getFlashBag();
                $entityManager = $this->getDoctrine()->getManager();

                try {
                    $entityManager->persist($post);
                    $entityManager->flush();

                    $flashBag->add('success', $translator->trans('icap_blog_post_edit_success', array(), 'icap_blog'));
                }
                catch(\Exception $exception)
                {
                    $flashBag->add('error', $translator->trans('icap_blog_post_edit_error', array(), 'icap_blog'));
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
}
