<?php

namespace ICAP\BlogBundle\Controller;

use Claroline\CoreBundle\Entity\User;
use ICAP\BlogBundle\Entity\Blog;
use ICAP\BlogBundle\Entity\BlogOptions;
use ICAP\BlogBundle\Entity\Post;
use ICAP\BlogBundle\Entity\Statusable;
use ICAP\BlogBundle\Form\BlogOptionsType;
use ICAP\BlogBundle\Entity\Tag;
use Pagerfanta\Adapter\DoctrineORMAdapter;
use Pagerfanta\Exception\NotValidCurrentPageException;
use Pagerfanta\Pagerfanta;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class BlogController extends Controller
{
    /**
     * @Route("/{blogId}/{page}", name="icap_blog_view", requirements={"blogId" = "\d+", "page" = "\d+"}, defaults={"page" = 1})
     * @Route("/{blogId}/{tagName}/{page}", name="icap_blog_view_by_tag", requirements={"blogId" = "\d+", "page" = "\d+"}, defaults={"page" = 1})
     * @ParamConverter("blog", class="ICAPBlogBundle:Blog", options={"id" = "blogId"})
     * @ParamConverter("tag", class="ICAPBlogBundle:Tag", options={"mapping": {"tagName": "name"}})
     * @ParamConverter("user", options={"authenticatedUser" = true})
     * @Template()
     */
    public function viewAction(Blog $blog, $page, User $user, Tag $tag = null)
    {
        $this->checkAccess("OPEN", $blog);

        $postRepository = $this->getDoctrine()->getRepository('ICAPBlogBundle:Post');

        /** @var \Doctrine\ORM\QueryBuilder $query */
        $query = $postRepository
            ->createQueryBuilder('post')
            ->andWhere('post.blog = :blogId')
        ;

        if(!$this->isUserGranted("EDIT", $blog)) {
            $query
                ->andWhere('post.publicationDate IS NOT NULL')
                ->andWhere('post.status = :publishedStatus')
                ->setParameter('publishedStatus', Statusable::STATUS_PUBLISHED)
            ;
        }

        if(null !== $tag) {
            $query
                ->join('post.tags', 't')
                ->andWhere('t.id = :tagId')
                ->setParameter('tagId', $tag->getId())
            ;
        }

        $query
            ->setParameter('blogId', $blog->getId())
            ->orderBy('post.publicationDate', 'ASC')
        ;

        $adapter = new DoctrineORMAdapter($query);
        $pager   = new PagerFanta($adapter);

        $pager->setMaxPerPage($blog->getOptions()->getPostPerPage());

        try {
            $pager->setCurrentPage($page);
        } catch (NotValidCurrentPageException $exception) {
            throw new NotFoundHttpException();
        }

        return array(
            '_resource' => $blog,
            'user'      => $user,
            'pager'     => $pager,
            'tag'       => $tag
        );
    }

    /**
     * @Route("/configure/{blogId}", name="icap_blog_configure", requirements={"blogId" = "\d+"})
     * @ParamConverter("blog", class="ICAPBlogBundle:Blog", options={"id" = "blogId"})
     * @Template()
     */
    public function configureAction(Request $request, Blog $blog)
    {
        $this->checkAccess("EDIT", $blog);

        $blogOptions = $blog->getOptions();

        $form = $this->createForm(new BlogOptionsType(), $blogOptions);

        if("POST" === $request->getMethod()) {
            $form->submit($request);
            if ($form->isValid()) {
                $entityManager = $this->getDoctrine()->getManager();
                $translator = $this->get('translator');
                $flashBag = $this->get('session')->getFlashBag();

                try {
                    $entityManager->persist($blogOptions);
                    $entityManager->flush();

                    $flashBag->add('success', $translator->trans('icap_blog_post_configure_success', array(), 'icap_blog'));
                }
                catch(\Exception $exception) {
                    $flashBag->add('error', $translator->trans('icap_blog_post_configure_error', array(), 'icap_blog'));
                }

                return $this->redirect($this->generateUrl('icap_blog_configure', array('blogId' => $blog->getId())));
            }
        }

        return array(
            '_resource' => $blog,
            'form'      => $form->createView()
        );
    }
}
