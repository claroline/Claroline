<?php

namespace Icap\BlogBundle\Controller;

use Claroline\CoreBundle\Entity\User;
use Icap\BlogBundle\Entity\Blog;
use Icap\BlogBundle\Entity\Post;
use Icap\BlogBundle\Entity\Statusable;
use Icap\BlogBundle\Exception\TooMuchResultException;
use Icap\BlogBundle\Form\BlogBannerType;
use Icap\BlogBundle\Form\BlogInfosType;
use Icap\BlogBundle\Form\BlogOptionsType;
use Icap\BlogBundle\Entity\Tag;
use Pagerfanta\Adapter\ArrayAdapter;
use Pagerfanta\Adapter\DoctrineORMAdapter;
use Pagerfanta\Exception\NotValidCurrentPageException;
use Pagerfanta\Pagerfanta;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class BlogController extends BaseController
{
    /**
     * @Route("/{blogId}/{page}", name="icap_blog_view", requirements={"blogId" = "\d+", "page" = "\d+"}, defaults={"page" = 1})
     * @Route("/{blogId}/{filter}/{page}", name="icap_blog_view_filter", requirements={"blogId" = "\d+", "page" = "\d+"}, defaults={"page" = 1})
     * @ParamConverter("blog", class="IcapBlogBundle:Blog", options={"id" = "blogId"})
     * @Template()
     */
    public function viewAction(Request $request, Blog $blog, $page, $filter = null)
    {
        $this->checkAccess('OPEN', $blog);

        $user = $this->get('security.token_storage')->getToken()->getUser();

        $search = $request->get('search');
        if (null !== $search && '' !== $search) {
            return $this->redirect($this->generateUrl('icap_blog_view_search', array('blogId' => $blog->getId(), 'search' => $search)));
        }

        /** @var \Icap\BlogBundle\Repository\PostRepository $postRepository */
        $postRepository = $this->get('icap.blog.post_repository');

        $tag = null;
        $author = null;
        $date = null;

        if (null !== $filter) {
            $tag = $this->get('icap.blog.tag_repository')->findOneBySlug($filter);

            if (null === $tag) {
                $author = $this->getDoctrine()->getRepository('ClarolineCoreBundle:User')->findOneByUsername($filter);

                if (null === $author) {
                    $date = $filter;
                }
            }
        }

        /** @var \Doctrine\ORM\QueryBuilder $query */
        $query = $postRepository
            ->createQueryBuilder('post')
            ->select(array('post', 'author'))
            ->join('post.author', 'author')
            ->andWhere('post.blog = :blogId')
        ;

        if (!$this->isUserGranted('EDIT', $blog)) {
            $query = $postRepository->filterByPublishPost($query);
        }

        $criterias = array(
            'tag' => $tag,
            'author' => $author,
            'date' => $date,
            'blogId' => $blog->getId(),
        );

        $query = $postRepository->createCriteriaQueryBuilder($criterias, $query);

        $adapter = new DoctrineORMAdapter($query, false);
        $pager = new PagerFanta($adapter);

        $pager->setMaxPerPage($blog->getOptions()->getPostPerPage());

        try {
            $pager->setCurrentPage($page);
        } catch (NotValidCurrentPageException $exception) {
            throw new NotFoundHttpException();
        }

        return array(
            '_resource' => $blog,
            'bannerForm' => $this->getBannerForm($blog->getOptions()),
            'user' => $user,
            'pager' => $pager,
            'tag' => $tag,
            'author' => $author,
            'date' => $date,
        );
    }

    /**
     * @Route("/{blogId}/search/{search}/{page}", name="icap_blog_view_search", requirements={"blogId" = "\d+", "page" = "\d+"}, defaults={"page" = 1})
     * @ParamConverter("blog", class="IcapBlogBundle:Blog", options={"id" = "blogId"})
     * @Template()
     */
    public function viewSearchAction(Blog $blog, $page, $search)
    {
        $this->checkAccess('OPEN', $blog);

        $user = $this->get('security.token_storage')->getToken()->getUser();

        /** @var \Icap\BlogBundle\Repository\PostRepository $postRepository */
        $postRepository = $this->get('icap.blog.post_repository');

        try {
            /** @var \Doctrine\ORM\QueryBuilder $query */
            $query = $postRepository->searchByBlog($blog, $search, false);

            if (!$this->isUserGranted('EDIT', $blog)) {
                $query
                    ->andWhere('post.publicationDate IS NOT NULL')
                    ->andWhere('post.status = :publishedStatus')
                    ->setParameter('publishedStatus', Statusable::STATUS_PUBLISHED)
                ;
            }

            $adapter = new DoctrineORMAdapter($query);
            $pager = new PagerFanta($adapter);

            $pager
                ->setMaxPerPage($blog->getOptions()->getPostPerPage())
                ->setCurrentPage($page)
            ;
        } catch (NotValidCurrentPageException $exception) {
            throw new NotFoundHttpException();
        } catch (TooMuchResultException $exception) {
            $this->get('session')->getFlashBag()->add('error', $this->get('translator')->trans('icap_blog_post_search_too_much_result', array(), 'icap_blog'));
            $adapter = new ArrayAdapter(array());
            $pager = new PagerFanta($adapter);

            $pager->setCurrentPage($page);
        }

        return array(
            '_resource' => $blog,
            'bannerForm' => $this->getBannerForm($blog->getOptions()),
            'user' => $user,
            'pager' => $pager,
            'search' => $search,
        );
    }

    /**
     * @Route("/pdf/{blogId}", name="icap_blog_view_pdf", requirements={"blogId" = "\d+"})
     * @ParamConverter("blog", class="IcapBlogBundle:Blog", options={"id" = "blogId"})     *
     */
    public function viewPdfAction(Blog $blog)
    {
        $this->checkAccess('OPEN', $blog);

        /** @var \Icap\BlogBundle\Repository\PostRepository $postRepository */
        $postRepository = $this->get('icap.blog.post_repository');

        $posts = $postRepository->findAllPublicByBlog($blog);

        $content = $this->renderView('IcapBlogBundle:Blog:view.pdf.twig',
            array(
                '_resource' => $blog,
                'posts' => $posts,
            )
        );

        return new Response(
            $this->get('knp_snappy.pdf')->getOutputFromHtml(
                $content,
                array(
                    'outline' => true,
                    'footer-right' => '[page]/[toPage]',
                    'footer-spacing' => 3,
                    'footer-font-size' => 8,
                ),
                true
            ),
            200,
            array(
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'inline; filename="'.$blog->getResourceNode()->getName(),
            )
        );
    }

    /**
     * @Route("/configure/{blogId}", name="icap_blog_configure", requirements={"blogId" = "\d+"})
     * @ParamConverter("blog", class="IcapBlogBundle:Blog", options={"id" = "blogId"})
     * @ParamConverter("user", options={"authenticatedUser" = true})
     * @Template()
     */
    public function configureAction(Request $request, Blog $blog, User $user)
    {
        $this->checkAccess('ADMINISTRATE', $blog);

        $blogOptions = $blog->getOptions();

        $form = $this->createForm(new BlogOptionsType(), $blogOptions);

        if ('POST' === $request->getMethod()) {
            $form->submit($request);
            if ($form->isValid()) {
                $entityManager = $this->getDoctrine()->getManager();
                $translator = $this->get('translator');
                $flashBag = $this->get('session')->getFlashBag();

                try {
                    $unitOfWork = $entityManager->getUnitOfWork();
                    $unitOfWork->computeChangeSets();
                    $changeSet = $unitOfWork->getEntityChangeSet($blogOptions);

                    $entityManager->persist($blogOptions);
                    $entityManager->flush();

                    $this->dispatchBlogConfigureEvent($blogOptions, $changeSet);

                    $flashBag->add('success', $translator->trans('icap_blog_post_configure_success', array(), 'icap_blog'));
                } catch (\Exception $exception) {
                    $flashBag->add('error', $translator->trans('icap_blog_post_configure_error', array(), 'icap_blog'));
                }

                return $this->redirect($this->generateUrl('icap_blog_configure', array('blogId' => $blog->getId())));
            }
        }

        return array(
            '_resource' => $blog,
            'bannerForm' => $this->getBannerForm($blog->getOptions()),
            'form' => $form->createView(),
            'user' => $user,
        );
    }

    /**
     * @Route("/banner/{blogId}", name="icap_blog_configure_banner", requirements={"blogId" = "\d+"})
     * @Method({"POST"})
     * @ParamConverter("blog", class="IcapBlogBundle:Blog", options={"id" = "blogId"})
     * @Template()
     */
    public function configureBannerAction(Request $request, Blog $blog)
    {
        $this->checkAccess('ADMINISTRATE', $blog);

        $blogOptions = $blog->getOptions();

        $form = $this->createForm(new BlogBannerType(), $blogOptions);

        $form->submit($request);
        if ($form->isValid()) {
            $this->container->get('icap_blog.manager.blog')->updateBanner(
                $form->get('file')->getData(),
                $blogOptions
            );
            $entityManager = $this->getDoctrine()->getManager();
            $translator = $this->get('translator');
            $flashBag = $this->get('session')->getFlashBag();

            try {
                $unitOfWork = $entityManager->getUnitOfWork();
                $unitOfWork->computeChangeSets();
                $changeSet = $unitOfWork->getEntityChangeSet($blogOptions);

                $entityManager->persist($blogOptions);
                $entityManager->flush();

                $this->dispatchBlogConfigureBannerEvent($blogOptions, $changeSet);

                $flashBag->add('success', $translator->trans('icap_blog_post_configure_banner_success', array(), 'icap_blog'));
            } catch (\Exception $exception) {
                $flashBag->add('error', $translator->trans('icap_blog_post_configure_banner_error', array(), 'icap_blog'));
            }
        }

        return $this->redirect($this->generateUrl('icap_blog_view', array('blogId' => $blog->getId())));
    }

    /**
     * @Route("/edit/{blogId}", name="icap_blog_edit_infos", requirements={"blogId" = "\d+"})
     * @ParamConverter("blog", class="IcapBlogBundle:Blog", options={"id" = "blogId"})
     * @ParamConverter("user", options={"authenticatedUser" = true})
     * @Template()
     */
    public function editAction(Request $request, Blog $blog, User $user)
    {
        $this->checkAccess('ADMINISTRATE', $blog);

        $form = $this->createForm(new BlogInfosType(), $blog);

        if ('POST' === $request->getMethod()) {
            $form->submit($request);
            if ($form->isValid()) {
                $entityManager = $this->getDoctrine()->getManager();
                $translator = $this->get('translator');
                $flashBag = $this->get('session')->getFlashBag();

                try {
                    $unitOfWork = $entityManager->getUnitOfWork();
                    $unitOfWork->computeChangeSets();
                    $changeSet = $unitOfWork->getEntityChangeSet($blog);

                    $entityManager->persist($blog);
                    $entityManager->flush();

                    $this->dispatchBlogUpdateEvent($blog, $changeSet);

                    $flashBag->add('success', $translator->trans('icap_blog_edit_infos_success', array(), 'icap_blog'));
                } catch (\Exception $exception) {
                    $flashBag->add('error', $translator->trans('icap_blog_edit_infos_error', array(), 'icap_blog'));
                }

                return $this->redirect($this->generateUrl('icap_blog_view', array('blogId' => $blog->getId())));
            }
        }

        return array(
            '_resource' => $blog,
            'bannerForm' => $this->getBannerForm($blog->getOptions()),
            'form' => $form->createView(),
            'user' => $user,
        );
    }

    /**
     * @Route("/rss/{blogId}", name="icap_blog_rss", requirements={"blogId" = "\d+"})
     * @ParamConverter("blog", class="IcapBlogBundle:Blog", options={"id" = "blogId"})
     */
    public function rssAction(Blog $blog)
    {
        $baseUrl = $this->get('request')->getSchemeAndHttpHost();

        $feed = array(
            'title' => $blog->getResourceNode()->getName(),
            'description' => $blog->getInfos(),
            'siteUrl' => $baseUrl.$this->generateUrl('icap_blog_view', array('blogId' => $blog->getId())),
            'feedUrl' => $baseUrl.$this->generateUrl('icap_blog_rss', array('blogId' => $blog->getId())),
            'lang' => $this->get('claroline.config.platform_config_handler')->getParameter('locale_language'),
        );

        /** @var \Icap\BlogBundle\Entity\Post[] $posts */
        $posts = $this->getDoctrine()->getRepository('IcapBlogBundle:Post')->findRssDatas($blog);

        $items = array();
        foreach ($posts as $post) {
            $items[] = array(
                'title' => $post->getTitle(),
                'url' => $baseUrl.$this->generateUrl('icap_blog_post_view', array('blogId' => $blog->getId(), 'postSlug' => $post->getSlug())),
                'date' => $post->getPublicationDate()->format('d/m/Y h:i:s'),
                'intro' => $post->getContent(),
                'author' => $post->getAuthor()->getFirstName() - $post->getAuthor()->getLastName(),
            );
        }

        return new Response($this->renderView('IcapBlogBundle:Blog:rss.html.twig', array(
                'feed' => $feed,
                'items' => $items,
            )), 200, array(
                'Content-Type' => 'application/rss+xml',
                'charset' => 'utf-8',
            ));
    }

    /**
     * @Route("/calendar/{blogId}", name="icap_blog_calendar_datas", requirements={"blogId" = "\d+"})
     * @ParamConverter("blog", class="IcapBlogBundle:Blog", options={"id" = "blogId"})
     */
    public function calendarDatas(Request $request, Blog $blog)
    {
        $requestParameters = $request->query->all();
        $startDate = $requestParameters['start'];
        $endDate = $requestParameters['end'];
        $calendarDatas = array();
        $calendarDatasTemp = array();

        /** @var \Icap\BlogBundle\Repository\PostRepository $postRepository */
        $postRepository = $this->getDoctrine()->getManager()->getRepository('IcapBlogBundle:Post');

        $posts = $postRepository->findCalendarDatas($blog, $startDate, $endDate);

        foreach ($posts as $post) {
            $publicationDate = $post->getPublicationDate()->format('Y-m-d');
            $publicationDateForSort = $post->getPublicationDate()->format('d-m-Y');

            if (!isset($calendarDatasTemp[$publicationDate])) {
                $calendarDatasTemp[$publicationDate] = array(
                    'id' => '12',
                    'start' => $publicationDate,
                    'title' => '1',
                    'url' => $this->generateUrl(
                        'icap_blog_view_filter',
                        array('blogId' => $blog->getId(), 'filter' => $publicationDateForSort)
                    ),
                );
            } else {
                $title = intval($calendarDatasTemp[$publicationDate]['title']);
                ++$title;
                $calendarDatasTemp[$publicationDate]['title'] = "$title";
            }
        }
        foreach ($calendarDatasTemp as $calendarData) {
            $calendarDatas[] = $calendarData;
        }

        $response = new JsonResponse($calendarDatas);

        return $response;
    }
}
