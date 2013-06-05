<?php

namespace ICAP\BlogBundle\Controller;

use ICAP\BlogBundle\Entity\Blog;
use Pagerfanta\Adapter\DoctrineCollectionAdapter;
use Pagerfanta\Pagerfanta;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;

class BlogController extends Controller
{
    /**
     * @Route(
     *      "/{blogId}/{page}",
     *      name="icap_blog_view",
     *      requirements={"resourceId" = "\d+", "page" = "\d+"}, defaults={"page" = 1}
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
}
