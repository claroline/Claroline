<?php

namespace ICAP\BlogBundle\Controller;

use Claroline\CoreBundle\Entity\User;
use ICAP\BlogBundle\Entity\Blog;
use ICAP\BlogBundle\Entity\BlogOptions;
use ICAP\BlogBundle\Entity\Post;
use ICAP\BlogBundle\Entity\Statusable;
use ICAP\BlogBundle\Exception\TooMuchResultException;
use ICAP\BlogBundle\Form\BlogInfosType;
use ICAP\BlogBundle\Form\BlogOptionsType;
use ICAP\BlogBundle\Entity\Tag;
use Pagerfanta\Adapter\ArrayAdapter;
use Pagerfanta\Adapter\DoctrineORMAdapter;
use Pagerfanta\Exception\NotValidCurrentPageException;
use Pagerfanta\Pagerfanta;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class BlogController extends Controller
{
    /**
     * @Route("/{blogId}/{page}", name="icap_blog_view", requirements={"blogId" = "\d+", "page" = "\d+"}, defaults={"page" = 1})
     * @Route("/{blogId}/{filter}/{page}", name="icap_blog_view_filter", requirements={"blogId" = "\d+", "page" = "\d+"}, defaults={"page" = 1})
     * @ParamConverter("blog", class="ICAPBlogBundle:Blog", options={"id" = "blogId"})
     * @ParamConverter("user", options={"authenticatedUser" = true})
     * @Template()
     */
    public function viewAction(Blog $blog, $page, User $user, $filter = null)
    {
        $this->checkAccess("OPEN", $blog);

        $search = $this->getRequest()->get('search');
        if(null !== $search && '' !== $search) {
            return $this->redirect($this->generateUrl('icap_blog_view_search', array('blogId' => $blog->getId(), 'search' => $search)));
        }

        $postRepository = $this->get('icap.blog.post_repository');

        $archivesDatas          = $postRepository->findArchiveDatasByBlog($blog);
        $displayedArchivesDatas = array();

        $translator = $this->get('translator');

        foreach($archivesDatas as $archivesData)
        {
            $displayedArchivesDatas[$archivesData['year']][] = array(
                'year'  => $archivesData['year'],
                'month' => $translator->trans('month.' . date("F", mktime(0, 0, 0, $archivesData['month'], 10)), array(), 'platform'),
                'count' => $archivesData['number']
            );
        }

        $tag    = null;
        $author = null;

        if(null !== $filter) {
            $tag = $this->get('icap.blog.tag_repository')->findOneByName($filter);

            if(null === $tag) {
                $author = $this->getDoctrine()->getRepository('ClarolineCoreBundle:User')->findOneByUsername($filter);
            }
        }

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
        elseif(null !== $author) {
            $query
                ->andWhere('post.author = :authorId')
                ->setParameter('authorId', $author->getId())
            ;
        }

        $query
            ->setParameter('blogId', $blog->getId())
            ->orderBy('post.publicationDate', 'DESC')
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
            'archives'  => $displayedArchivesDatas
        );
    }

    /**
     * @Route("/{blogId}/search/{search}/{page}", name="icap_blog_view_search", requirements={"blogId" = "\d+", "page" = "\d+"}, defaults={"page" = 1})
     * @ParamConverter("blog", class="ICAPBlogBundle:Blog", options={"id" = "blogId"})
     * @ParamConverter("user", options={"authenticatedUser" = true})
     * @Template()
     */
    public function viewSearchAction(Blog $blog, $page, User $user, $search)
    {
        $this->checkAccess("OPEN", $blog);

        /** @var \ICAp\BlogBundle\Repository\PostRepository $postRepository */
        $postRepository = $this->get('icap.blog.post_repository');

        try
        {
            /** @var \Doctrine\ORM\QueryBuilder $query */
            $query = $postRepository->searchByBlog($blog, $search, false);

            if(!$this->isUserGranted("EDIT", $blog)) {
                $query
                    ->andWhere('post.publicationDate IS NOT NULL')
                    ->andWhere('post.status = :publishedStatus')
                    ->setParameter('publishedStatus', Statusable::STATUS_PUBLISHED)
                ;
            }

            $adapter = new DoctrineORMAdapter($query);
            $pager   = new PagerFanta($adapter);

            $pager
                ->setMaxPerPage($blog->getOptions()->getPostPerPage())
                ->setCurrentPage($page)
            ;
        }
        catch (NotValidCurrentPageException $exception) {
            throw new NotFoundHttpException();
        }
        catch(TooMuchResultException $exception) {
            $this->get('session')->getFlashBag()->add('alert', $this->get('translator')->trans('icap_blog_post_search_too_much_result', array(), 'icap_blog'));
            $adapter = new ArrayAdapter(array());
            $pager   = new PagerFanta($adapter);

            $pager->setCurrentPage($page);
        }

        return array(
            '_resource' => $blog,
            'user'      => $user,
            'pager'     => $pager,
            'search'    => $search
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

    /**
     * @Route("/edit/{blogId}", name="icap_blog_edit_infos", requirements={"blogId" = "\d+"})
     * @ParamConverter("blog", class="ICAPBlogBundle:Blog", options={"id" = "blogId"})
     * @Template()
     */
    public function editAction(Request $request, Blog $blog)
    {
        $this->checkAccess("EDIT", $blog);

        $form = $this->createForm(new BlogInfosType(), $blog);

        if("POST" === $request->getMethod()) {
            $form->submit($request);
            if ($form->isValid()) {
                $entityManager = $this->getDoctrine()->getManager();
                $translator = $this->get('translator');
                $flashBag = $this->get('session')->getFlashBag();

                try {
                    $entityManager->persist($blog);
                    $entityManager->flush();

                    $flashBag->add('success', $translator->trans('icap_blog_edit_infos_success', array(), 'icap_blog'));
                }
                catch(\Exception $exception) {
                    $flashBag->add('error', $translator->trans('icap_blog_edit_infos_error', array(), 'icap_blog'));
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
     * @Route("/calendar/{blogId}", name="icap_blog_calendar_datas", requirements={"blogId" = "\d+"})
     * @ParamConverter("blog", class="ICAPBlogBundle:Blog", options={"id" = "blogId"})
     */
    public function calendarDatas(Request $request, Blog $blog)
    {
        $requestParameters = $request->query->all();
        $startDate         = $requestParameters['start'];
        $endDate           = $requestParameters['end'];
        $calendarDatas     = array();

        $postRepository = $this->getDoctrine()->getManager()->getRepository('ICAPBlogBundle:Post');

        $posts = $postRepository->findPublishedByBlogAndDates($blog, $startDate, $endDate);

        foreach($posts as $post)
        {
            $calendarDatas[] = array(
                'id'    => $post->getId(),
                'start' => $post->getPublicationDate()->format('Y-m-d'),
                'title' => $post->getTitle(),
                'url'   => $this->generateUrl('icap_blog_post_view', array('blogId' => $blog->getId(), 'postSlug' => $post->getSlug()))
            );
        }

        $response = new JsonResponse($calendarDatas);

        return $response;
    }
}
