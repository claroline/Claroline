<?php

namespace Icap\BlogBundle\Controller\API;

use Claroline\CoreBundle\Entity\User;
use FOS\RestBundle\Controller\Annotations\Get;
use FOS\RestBundle\Controller\Annotations\NamePrefix;
use FOS\RestBundle\Controller\Annotations\QueryParam;
use FOS\RestBundle\Controller\Annotations\RequestParam;
use FOS\RestBundle\Controller\Annotations\Route;
use FOS\RestBundle\Controller\Annotations\View;
use FOS\RestBundle\Request\ParamFetcher;
use Icap\BlogBundle\Controller\BaseController;
use Icap\BlogBundle\Entity\Blog;
use Icap\BlogBundle\Entity\BlogOptions;
use Icap\BlogBundle\Entity\Comment;
use Icap\BlogBundle\Entity\Post;
use Icap\BlogBundle\Entity\Statusable;
use Icap\BlogBundle\Entity\Tag;
use Pagerfanta\Adapter\DoctrineORMAdapter;
use Pagerfanta\Pagerfanta;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

/**
 * @NamePrefix("icap_blog_api_")
 */
class ApiController extends BaseController
{
    /**
     * Get blog info.
     *
     * @Route(requirements={ "blog" = "\d+" })
     */
    public function getBlogAction(Blog $blog)
    {
        $info = $blog->getInfos() === null ? '' : $blog->getInfos();

        return ['info' => $info];
    }

    /**
     * Update blog info.
     *
     * @Route(requirements={ "blog" = "\d+" })
     *
     * @RequestParam(name="info", allowBlank=false)
     */
    public function putBlogAction(Blog $blog, ParamFetcher $paramFetcher)
    {
        $blog->setInfos($paramFetcher->get('info'));

        $em = $this->getDoctrine()->getManager();
        $unitOfWork = $em->getUnitOfWork();
        $unitOfWork->computeChangeSets();
        $changeSet = $unitOfWork->getEntityChangeSet($blog);

        $em->persist($blog);
        $em->flush();

        $this->dispatchBlogUpdateEvent($blog, $changeSet);

        return ['info' => $blog->getInfos()];
    }

    /**
     * Get blog options.
     *
     * @Route(requirements={ "blog" = "\d+" })
     */
    public function getBlogOptionsAction(Blog $blog)
    {
        $this->checkAccess('ADMINISTRATE', $blog);

        return $blog->getOptions();
    }

    /**
     * Update blog options.
     *
     * @Route(requirements={ "blog" = "\d+" }, defaults={ "options" = null })
     *
     * @ParamConverter("options", converter="fos_rest.request_body")
     */
    public function putBlogOptionsAction(Blog $blog, BlogOptions $options)
    {
        $this->checkAccess('ADMINISTRATE', $blog);

        $unitOfWork = $this->get('icap_blog.manager.blog')->updateOptions($blog, $options);

        // Dispatch event
        $unitOfWork->computeChangeSets();
        $changeSet = $unitOfWork->getEntityChangeSet($blog->getOptions());

        $this->dispatchBlogConfigureEvent($blog->getOptions(), $changeSet);

        return $blog->getOptions();
    }

    /**
     * Get all tags for a given blog.
     *
     * @Route(requirements={ "blog" = "\d+" })
     */
    public function getBlogTagsAction(Blog $blog)
    {
        $this->checkAccess('OPEN', $blog);

        $tagManager = $this->get('icap.blog.manager.tag');

        if ($blog->getOptions()->isTagTopMode()) {
            $tags = $tagManager->loadByBlog($blog, $blog->getOptions()->getMaxTag());
        } else {
            $tags = $tagManager->loadByBlog($blog);
            shuffle($tags);
        }

        return $tags;
    }

    /**
     * Get all authors for a given blog.
     *
     * @Route(requirements={ "blog" = "\d+" })
     */
    public function getBlogAuthorsAction(Blog $blog)
    {
        $this->checkAccess('OPEN', $blog);

        return $this->get('icap.blog.post_repository')->findAuthorsByBlog($blog);
    }

    /**
     * Get all archives for a given blog.
     *
     * @Route(requirements={ "blog" = "\d+" })
     */
    public function getBlogArchivesAction(Blog $blog)
    {
        $this->checkAccess('OPEN', $blog);

        return $this->getArchiveDatas($blog);
    }

    /**
     * Get a post by id or slug.
     *
     * @Route(requirements={ "blog" = "\d+", "postId" = ".+" })
     *
     * @View(serializerGroups={ "blog_post", "api_user_min" })
     */
    public function getBlogPostAction(Request $request, Blog $blog, $postId)
    {
        $this->checkAccess('OPEN', $blog);

        $post = null;
        if (preg_match('/^\d+$/', $postId)) {
            $post = $this->get('icap.blog.post_repository')->findOneBy([
                'blog' => $blog,
                'id' => $postId,
            ]);
        } else {
            $post = $this->get('icap.blog.post_repository')->findOneBy([
                'blog' => $blog,
                'slug' => $postId,
            ]);
        }

        if (is_null($post)) {
            throw new NotFoundHttpException();
        }

        $this->dispatchPostReadEvent($post);

        $session = $request->getSession();
        $sessionViewCounterKey = 'blog_post_view_counter_'.$postId;
        $now = time();
        $notRepeatableLogTimeInSeconds = $this->container->getParameter(
            'non_repeatable_log_time_in_seconds'
        );

        if ($now >= ($session->get($sessionViewCounterKey) + $notRepeatableLogTimeInSeconds)) {
            $em = $this->getDoctrine()->getManager();
            $post->increaseViewCounter();
            $session->set($sessionViewCounterKey, $now);
            $em->persist($post);
            $em->flush();
        }

        return $post;
    }

    /**
     * Get multiple posts (paged response).
     *
     * @Route(requirements={ "blog" = "\d+" })
     *
     * @QueryParam(name="page", requirements="\d+", allowBlank=true, default="1")
     *
     * @View(serializerGroups={ "blog_list", "api_user_min" })
     */
    public function getBlogPostsAction(Blog $blog, ParamFetcher $paramFetcher)
    {
        $this->checkAccess('OPEN', $blog);

        return $this->get('icap.blog.manager.post')->getPostsPaged(
            $blog,
            $paramFetcher->get('page'),
            $this->get('security.authorization_checker')->isGranted('ADMIN', $blog)
        );
    }

    /**
     * Get multiple posts for a given tag (by ID or slug).
     *
     * @Route(requirements={ "blog" = "\d+", "tagId" = ".+" })
     *
     * @QueryParam(name="page", requirements="\d+", allowBlank=true, default="1")
     *
     * @View(serializerGroups={ "blog_list", "api_user_min" })
     */
    public function getBlogTagsPostsAction(Blog $blog, $tagId, ParamFetcher $paramFetcher)
    {
        $this->checkAccess('OPEN', $blog);

        $tag = null;
        if (preg_match('/^\d+$/', $tagId)) {
            $tag = $this->get('icap.blog.tag_repository')->findOneById($tagId);
        } else {
            $tag = $this->get('icap.blog.tag_repository')->findOneBySlug($tagId);
        }

        $results = $this->get('icap.blog.manager.post')->getPostsByTagPaged(
            $blog,
            $tag,
            !$this->isUserGranted('EDIT', $blog),
            $paramFetcher->get('page')
        );

        // Append the searched tag to the result
        $results['tag'] = $tag;

        return $results;
    }

    /**
     * Get multiple posts for a given author ID.
     *
     * @Route(requirements={ "blog" = "\d+", "author" = "\d+" })
     *
     * @QueryParam(name="page", requirements="\d+", allowBlank=true, default="1")
     *
     * @View(serializerGroups={ "blog_list", "api_user_min" })
     */
    public function getBlogAuthorsPostsAction(Blog $blog, User $author, ParamFetcher $paramFetcher)
    {
        $this->checkAccess('OPEN', $blog);

        $results = $this->get('icap.blog.manager.post')->getPostsByAuthorPaged(
            $blog,
            $author,
            !$this->isUserGranted('EDIT', $blog),
            $paramFetcher->get('page')
        );

        // Append the user object to the result
        $results['author'] = $author;

        return $results;
    }

    /**
     * Get multiple posts for a given publication day.
     *
     * @Route(requirements={ "blog" = "\d+", "day" = "\d{2}-\d{2}-\d{4}" })
     *
     * @QueryParam(name="page", requirements="\d+", allowBlank=true, default="1")
     *
     * @View(serializerGroups={ "blog_list", "api_user_min" })
     */
    public function getBlogDaysPostsAction(Blog $blog, $day, ParamFetcher $paramFetcher)
    {
        $this->checkAccess('OPEN', $blog);

        return $this->getPostsByDate($blog, $day, $paramFetcher);
    }

    /**
     * Get multiple posts for a given publication month.
     *
     * @Route(requirements={ "blog" = "\d+", "month" = "\d{2}-\d{4}" })
     *
     * @QueryParam(name="page", requirements="\d+", allowBlank=true, default="1")
     *
     * @View(serializerGroups={ "blog_list", "api_user_min" })
     */
    public function getBlogMonthsPostsAction(Blog $blog, $month, ParamFetcher $paramFetcher)
    {
        $this->checkAccess('OPEN', $blog);

        return $this->getPostsByDate($blog, $month, $paramFetcher);
    }

    /**
     * Create a post.
     *
     * @Route(requirements={ "blog" = "\d+" })
     *
     * @RequestParam(name="title", allowBlank=false)
     * @RequestParam(name="content", allowBlank=false)
     * @RequestParam(name="publication_date", nullable=true)
     * @RequestParam(name="tags", allowBlank=false, array=true)
     *
     * @View(serializerGroups={ "blog_list", "api_user_min" })
     */
    public function postBlogPostAction(Blog $blog, ParamFetcher $paramFetcher)
    {
        $this->checkAccess(['EDIT', 'POST'], $blog, 'OR');

        $em = $this->getDoctrine()->getManager();
        $user = $this->get('security.token_storage')->getToken()->getUser();

        $post = new Post();
        $post
            ->setBlog($blog)
            // User has already been checked and is logged in
            ->setAuthor($user)
            ->setStatus($blog->isAutoPublishPost() ? Post::STATUS_PUBLISHED : Post::STATUS_UNPUBLISHED)
            ->setTitle($paramFetcher->get('title'))
            ->setContent($paramFetcher->get('content'));

        if ($paramFetcher->get('publication_date') !== null) {
            $post->setPublicationDate(new \DateTime($paramFetcher->get('publication_date')));
        }

        // Tags
        $newTags = $paramFetcher->get('tags');
        $tagManager = $this->get('icap.blog.manager.tag');

        // Add new tags
        foreach ($newTags as $newTag) {
            $tag = $tagManager->loadOrCreateTag($newTag['text']);
            $post->addTag($tag);
        }

        $em->persist($post);
        $em->flush();

        $this->dispatchPostCreateEvent($blog, $post);

        if ($user !== 'anon.') {
            $this->updateResourceTracking($blog->getResourceNode(), $user, new \DateTime());
        }

        return $post;
    }

    /**
     * Update a post.
     *
     * @Route(requirements={ "blog" = "\d+" })
     *
     * @RequestParam(name="title", allowBlank=false)
     * @RequestParam(name="content", allowBlank=false)
     * @RequestParam(name="publication_date", nullable=true)
     * @RequestParam(name="tags", allowBlank=false, array=true)
     *
     * @View(serializerGroups={ "blog_list", "api_user_min" })
     */
    public function putBlogPostAction(Blog $blog, $post, ParamFetcher $paramFetcher)
    {
        $this->checkAccess(['EDIT', 'POST'], $blog, 'OR');

        $myPost = $this->get('icap.blog.post_repository')->findOneBy([
            'blog' => $blog,
            'id' => $post,
        ]);

        if (is_null($myPost)) {
            throw new NotFoundHttpException();
        }

        $em = $this->getDoctrine()->getManager();

        $myPost
            ->setTitle($paramFetcher->get('title'))
            ->setContent($paramFetcher->get('content'));

        if ($paramFetcher->get('publication_date') !== null) {
            $myPost->setPublicationDate(new \DateTime($paramFetcher->get('publication_date')));
        } else {
            $myPost->setPublicationDate(null);
        }

        // Tags
        $oldTags = $myPost->getTags();
        $newTags = $paramFetcher->get('tags');

        // Remove old tags
        foreach ($oldTags as $tag) {
            $myPost->removeTag($tag);
        }

        $tagManager = $this->get('icap.blog.manager.tag');

        // Add new tags
        foreach ($newTags as $tag) {
            $myTag = $tagManager->loadOrCreateTag($tag['text']);
            $myPost->addTag($myTag);
        }

        $em->persist($myPost);
        $em->flush();

        $unitOfWork = $em->getUnitOfWork();
        $unitOfWork->computeChangeSets();
        $changeSet = $unitOfWork->getEntityChangeSet($myPost);

        $this->dispatchPostUpdateEvent($myPost, $changeSet);

        $user = $this->get('security.token_storage')->getToken()->getUser();

        if ($user !== 'anon.') {
            $this->updateResourceTracking($blog->getResourceNode(), $user, new \DateTime());
        }

        return $myPost;
    }

    /**
     * Change status of a post.
     *
     * @Route(requirements={ "blog" = "\d+", "post" = "\d+" })
     *
     * @RequestParam(name="is_published")
     *
     * @View(serializerGroups={ "blog_list", "api_user_min" })
     */
    public function putBlogPostVisibilityAction(Blog $blog, $post, ParamFetcher $paramFetcher)
    {
        $this->checkAccess(['EDIT', 'POST'], $blog, 'OR');

        $myPost = $this->get('icap.blog.post_repository')->findOneBy([
            'blog' => $blog,
            'id' => $post,
        ]);

        if (is_null($myPost)) {
            throw new NotFoundHttpException();
        }

        if ($paramFetcher->get('is_published') === true) {
            $myPost->publish();
        } else {
            $myPost->unpublish();
        }

        $em = $this->getDoctrine()->getManager();
        $em->persist($myPost);
        $em->flush();

        $unitOfWork = $em->getUnitOfWork();
        $unitOfWork->computeChangeSets();
        $changeSet = $unitOfWork->getEntityChangeSet($myPost);

        $this->dispatchPostUpdateEvent($myPost, $changeSet);

        return $myPost;
    }

    /**
     * Delete a post.
     *
     * @Route(requirements={ "blog" = "\d+" })
     */
    public function deleteBlogPostAction(Blog $blog, $post)
    {
        $this->checkAccess(['EDIT', 'POST'], $blog, 'OR');

        $myPost = $this->get('icap.blog.post_repository')->findOneBy([
            'blog' => $blog,
            'id' => $post,
        ]);

        if (is_null($myPost)) {
            throw new NotFoundHttpException();
        }

        $em = $this->getDoctrine()->getManager();
        $em->remove($myPost);
        $em->flush();

        $this->dispatchPostDeleteEvent($myPost);
    }

    /**
     * Get all the tags for a post.
     *
     * @Route(requirements={ "blog" = "\d+", "post" = "\d+" })
     *
     * @View(serializerGroups={ "blog_list" })
     */
    public function getBlogPostTagsAction(Blog $blog, $post)
    {
        $this->checkAccess('OPEN', $blog);

        $myPost = $this->get('icap.blog.post_repository')->findOneBy([
            'blog' => $blog,
            'id' => $post,
        ]);

        if (is_null($myPost)) {
            throw new NotFoundHttpException();
        }

        return $myPost->getTags();
    }

    /**
     * Get all the comments for a post.
     *
     * @Route(requirements={ "blog" = "\d+", "post" = "\d+" })
     *
     * @View(serializerGroups={ "blog_list", "api_user_min" })
     */
    public function getBlogPostCommentsAction(Blog $blog, $post)
    {
        $this->checkAccess('OPEN', $blog);

        $myPost = $this->get('icap.blog.post_repository')->findOneBy([
            'blog' => $blog,
            'id' => $post,
        ]);

        if (is_null($myPost)) {
            throw new NotFoundHttpException();
        }

        return $myPost->getComments();
    }

    /**
     * Create a comment.
     *
     * @Route(requirements={ "blog" = "\d+", "post" = "\d+" })
     *
     * @RequestParam(name="message", allowBlank=false)
     *
     * @View(serializerGroups={ "blog_list", "api_user_min" })
     */
    public function postBlogPostCommentAction(Blog $blog, Post $post, ParamFetcher $paramFetcher)
    {
        $this->checkAccess('OPEN', $blog);

        // Are comments allowed?
        if (!$blog->isCommentsAuthorized() || (!$this->isLoggedIn() && !$blog->isAuthorizeAnonymousComment())) {
            throw new AccessDeniedException();
        }

        $myPost = $this->get('icap.blog.post_repository')->findOneBy([
            'blog' => $blog,
            'id' => $post,
        ]);

        if (is_null($myPost)) {
            throw new NotFoundHttpException();
        }

        $comment = new Comment();
        $comment
            ->setPost($post)
            ->setPublicationDate(new \DateTime())
            ->setStatus($blog->isAutoPublishComment() ? Comment::STATUS_PUBLISHED : Comment::STATUS_UNPUBLISHED)
            ->setMessage($paramFetcher->get('message'));

        $user = $this->get('security.token_storage')->getToken()->getUser();
        if (is_a($user, 'Claroline\\CoreBundle\\Entity\\User')) {
            $comment->setAuthor($user);
        }

        $em = $this->getDoctrine()->getManager();
        $em->persist($comment);
        $em->flush();

        $this->dispatchCommentCreateEvent($myPost, $comment);

        if ($user !== 'anon.') {
            $this->updateResourceTracking($blog->getResourceNode(), $user, new \DateTime());
        }

        return $comment;
    }

    /**
     * Update a comment.
     *
     * @Route(requirements={ "blog" = "\d+", "post" = "\d+", "post" = "\d+" })
     *
     * @RequestParam(name="message", allowBlank=false)
     *
     * @View(serializerGroups={ "blog_list", "api_user_min" })
     */
    public function putBlogPostCommentAction(Blog $blog, $post, $comment, ParamFetcher $paramFetcher)
    {
        $myPost = $this->get('icap.blog.post_repository')->findOneBy([
            'blog' => $blog,
            'id' => $post,
        ]);

        $myComment = $this->get('icap.blog.comment_repository')->findOneBy([
            'post' => $myPost,
            'id' => $comment,
        ]);

        if (is_null($myComment)) {
            throw new NotFoundHttpException();
        }

        $user = $this->get('security.token_storage')->getToken()->getUser();
        $author = $myComment->getAuthor();

        if (!is_a($user, 'Claroline\\CoreBundle\\Entity\\User') || (!is_null($author) && $user->getId() !== $author->getId())) {
            throw new AccessDeniedException($this->get('translator')->trans('icap_blog_comment_access_denied', [], 'icap_blog'));
        }

        $myComment->setMessage($paramFetcher->get('message'));

        $em = $this->getDoctrine()->getManager();
        $em->persist($myComment);
        $em->flush();

        $unitOfWork = $em->getUnitOfWork();
        $unitOfWork->computeChangeSets();
        $changeSet = $unitOfWork->getEntityChangeSet($myComment);

        $this->dispatchCommentUpdateEvent($myPost, $myComment, $changeSet);

        return $myComment;
    }

    /**
     * Delete a comment.
     *
     * @Route(requirements={ "blog" = "\d+", "post" = "\d+", "comment" = "\d+" })
     * @View(serializerGroups={ "blog_list", "api_user_min" })
     */
    public function deleteBlogPostCommentAction(Blog $blog, $post, $comment)
    {
        $this->checkAccess('EDIT', $blog);

        $myPost = $this->get('icap.blog.post_repository')->findOneBy([
            'blog' => $blog,
            'id' => $post,
        ]);

        $myComment = $this->get('icap.blog.comment_repository')->findOneBy([
            'post' => $myPost,
            'id' => $comment,
        ]);

        if (is_null($myComment)) {
            throw new NotFoundHttpException();
        }

        $em = $this->getDoctrine()->getManager();
        $em->remove($myComment);
        $em->flush();

        $this->dispatchCommentDeleteEvent($myPost, $myComment);

        return $myPost;
    }

    /**
     * Toggle status of a comment.
     *
     * @Route(requirements={ "blog" = "\d+", "post" = "\d+", "comment" = "\d+" })
     *
     * @RequestParam(name="is_published")
     *
     * @View(serializerGroups={ "blog_list", "api_user_min" })
     */
    public function putBlogPostCommentVisibilityAction(Blog $blog, $post, $comment, ParamFetcher $paramFetcher)
    {
        $this->checkAccess(['EDIT', 'POST'], $blog, 'OR');

        $myPost = $this->get('icap.blog.post_repository')->findOneBy([
            'blog' => $blog,
            'id' => $post,
        ]);

        $myComment = $this->get('icap.blog.comment_repository')->findOneBy([
            'post' => $myPost,
            'id' => $comment,
        ]);

        if (is_null($myComment)) {
            throw new NotFoundHttpException();
        }

        if ($paramFetcher->get('is_published') === true) {
            $myComment->publish();
        } else {
            $myComment->unpublish();
        }

        $em = $this->getDoctrine()->getManager();
        $em->persist($myComment);
        $em->flush();

        $this->dispatchCommentPublishEvent($myPost, $myComment);

        return $myComment;
    }

    /**
     * Upload a new banner image.
     *
     * @Route(requirements={ "blog" = "\d+" })
     */
    public function postBlogBannerAction(Request $request, Blog $blog)
    {
        $this->checkAccess('ADMINISTRATE', $blog);

        $blogOptions = $blog->getOptions();

        foreach ($request->files as $uploadedFile) {
            $this->container->get('icap_blog.manager.blog')->updateBanner(
                $uploadedFile,
                $blogOptions
            );

            $em = $this->getDoctrine()->getManager();
            $unitOfWork = $em->getUnitOfWork();
            $unitOfWork->computeChangeSets();
            $changeSet = $unitOfWork->getEntityChangeSet($blogOptions);

            $this->dispatchBlogConfigureBannerEvent($blogOptions, $changeSet);

            return $blog->getOptions()->getBannerBackgroundImage();
        }

        throw new BadRequestHttpException();
    }

    /**
     * Remove the current blog banner.
     *
     * @Route(requirements={ "blog" = "\d+" })
     */
    public function deleteBlogBannersAction(Blog $blog)
    {
        $this->checkAccess('ADMINISTRATE', $blog);

        $blogOptions = $blog->getOptions();

        $this->container->get('icap_blog.manager.blog')->updateBanner(
            null,
            $blogOptions
        );

        $em = $this->getDoctrine()->getManager();
        $unitOfWork = $em->getUnitOfWork();
        $unitOfWork->computeChangeSets();
        $changeSet = $unitOfWork->getEntityChangeSet($blogOptions);

        $this->dispatchBlogConfigureBannerEvent($blogOptions, $changeSet);

        return;
    }

    /**
     * Search in posts.
     *
     * @Route(requirements={ "blog" = "\d+", "search" = ".+" })
     *
     * @QueryParam(name="page", requirements="\d+", allowBlank=true, default="1")
     *
     * @View(serializerGroups={ "blog_list", "api_user_min" })
     */
    public function getBlogSearchAction(Blog $blog, $search, ParamFetcher $paramFetcher)
    {
        $this->checkAccess('OPEN', $blog);

        $page = $paramFetcher->get('page');

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
            $adapter = new ArrayAdapter([]);
            $pager = new PagerFanta($adapter);

            $pager->setCurrentPage($page);
        }

        // Pagerfanta returns a traversable object, not directly serializable
        $posts = [];
        foreach ($pager->getCurrentPageResults() as $post) {
            $posts[] = $post;
        }

        return [
            'total' => $pager->getNbResults(),
            'count' => count($posts),
            'posts' => $posts,
        ];
    }

    /**
     * Count posts per months.
     *
     * @Route(requirements={ "blog" = "\d+" })
     */
    public function getArchiveData(Blog $blog)
    {
        $postDatas = $this->get('icap.blog.post_repository')->findArchiveDatasByBlog($blog);
        $archiveDatas = [];

        $translator = $this->get('translator');

        foreach ($postDatas as $postData) {
            $publicationDate = $postData->getPublicationDate();
            $year = $publicationDate->format('Y');
            $month = $publicationDate->format('m');

            if (!isset($archiveDatas[$year][$month])) {
                $archiveDatas[$year][$month] = [
                    'year' => $year,
                    'month' => $translator->trans('month.'.date('F', mktime(0, 0, 0, $month, 10)), [], 'platform'),
                    'count' => 1,
                    'urlParameters' => $month.'-'.$year,
                ];
            } else {
                ++$archiveDatas[$year][$month]['count'];
            }
        }

        return $archiveDatas;
    }

    /**
     * @param Blog $blog
     * @param $date
     * @param ParamFetcher $paramFetcher
     *
     * @return mixed
     */
    private function getPostsByDate(Blog $blog, $date, ParamFetcher $paramFetcher)
    {
        return $this->get('icap.blog.manager.post')->getPostsByDatePaged(
            $blog,
            $date,
            !$this->isUserGranted('EDIT', $blog),
            $paramFetcher->get('page')
        );
    }

    /**
     * Is the user logged in or not ?
     *
     * @return User
     */
    private function isLoggedIn()
    {
        $user = $this->get('security.token_storage')->getToken()->getUser();

        return is_string($user) ? false : true;
    }
}
