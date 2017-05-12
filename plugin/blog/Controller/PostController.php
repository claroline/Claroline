<?php

namespace Icap\BlogBundle\Controller;

use Icap\BlogBundle\Entity\Blog;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

class PostController extends BaseController
{
    /**
     * This function is kept for backwards compatibility and redirects old URLS to the new angularized ones.
     *
     * @Route(
     *     "/{blogId}/post/view/{postSlug}",
     *     name="icap_blog_post_view",
     *     requirements={"id" = "\d+"}
     * )
     *
     * @ParamConverter("blog", class="IcapBlogBundle:Blog", options={"id" = "blogId"})
     * @Template()
     */
    public function viewAction(Blog $blog, $postSlug)
    {
        return $this->redirect($this->generateUrl('icap_blog_view', ['blogId' => $blog->getId()]).'#/'.$postSlug);
    }
}
