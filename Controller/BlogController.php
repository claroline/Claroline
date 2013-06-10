<?php

namespace ICAP\BlogBundle\Controller;

use ICAP\BlogBundle\Entity\Blog;
use ICAP\BlogBundle\Entity\Post;
use ICAP\BlogBundle\Form\PostType;
use Pagerfanta\Adapter\DoctrineCollectionAdapter;
use Pagerfanta\Pagerfanta;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\HttpFoundation\Request;

class BlogController extends Controller
{
    /**
     * @Route(
     *      "/{blogId}/{page}",
     *      name="icap_blog_view",
     *      requirements={"blogId" = "\d+", "page" = "\d+"}, defaults={"page" = 1}
     * )
     * @ParamConverter("blog", class="ICAPBlogBundle:Blog", options={"id" = "blogId"})
     * @Template()
     */
    public function viewAction(Blog $blog, $page)
    {
        $adapter = new DoctrineCollectionAdapter($blog->getPosts());
        $pager   = new Pagerfanta($adapter);

        return array(
            '_resource' => $blog,
            'pager'     => $pager
        );
    }

    /**
     * @Route(
     *      "/{blogId}/post/new",
     *      name="icap_blog_post_new",
     *      requirements={"blogId" = "\d+"}
     * )
     * @ParamConverter("blog", class="ICAPBlogBundle:Blog", options={"id" = "blogId"})
     * @Template()
     */
    public function newPostAction(Request $request, Blog $blog)
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
