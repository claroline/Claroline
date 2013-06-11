<?php

namespace ICAP\BlogBundle\Controller;

use ICAP\BlogBundle\Entity\Blog;
use ICAP\BlogBundle\Entity\BlogOptions;
use ICAP\BlogBundle\Entity\Post;
use ICAP\BlogBundle\Form\PostType;
use ICAP\BlogBundle\Form\BlogOptionsType;
use Pagerfanta\Adapter\DoctrineCollectionAdapter;
use Pagerfanta\Pagerfanta;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\HttpFoundation\Request;

class PostController extends Controller
{
    /**
     * @Route(
     *      "/{blogId}/post/new",
     *      name="icap_blog_post_new",
     *      requirements={"blogId" = "\d+"}
     * )
     * @ParamConverter("blog", class="ICAPBlogBundle:Blog", options={"id" = "blogId"})
     * @Template()
     */
    public function newAction(Request $request, Blog $blog)
    {
        $form = $this->createForm(new PostType(), new Post());

        if("POST" === $request->getMethod()) {
            $form->bind($request);
            if ($form->isValid()) {
                return $this->redirect($this->generateUrl('icap_blog_view', array('blogId' => $blog->getId())));
            }
        }

        return array(
            '_resource' => $blog,
            'form'      => $form->createView()
        );
    }
}
