<?php

namespace Icap\BlogBundle\Manager;

use Claroline\AppBundle\API\FinderProvider;
use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Entity\User;
use Icap\BlogBundle\Entity\Blog;
use Icap\BlogBundle\Entity\BlogOptions;
use Icap\BlogBundle\Entity\Comment;
use Icap\BlogBundle\Entity\Member;
use Icap\BlogBundle\Entity\Post;

class CommentManager
{
    /**
     * @var ObjectManager
     */
    private $om;
    private $finder;
    protected $repo;
    protected $memberRepo;
    private $trackingManager;

    public function __construct(
        ObjectManager $om,
        FinderProvider $finder,
        BlogTrackingManager $trackingManager)
    {
        $this->om = $om;
        $this->finder = $finder;
        $this->trackingManager = $trackingManager;

        $this->repo = $this->om->getRepository(Comment::class);
        $this->memberRepo = $this->om->getRepository(Member::class);
    }

    /**
     * Find all content for a given user and the replace him by another.
     *
     * @return int
     */
    public function replaceCommentAuthor(User $from, User $to)
    {
        $comments = $this->repo->findByAuthor($from);

        if (count($comments) > 0) {
            foreach ($comments as $comment) {
                $comment->setAuthor($to);
            }

            $this->om->flush();
        }

        return count($comments);
    }

    /**
     * Get unpublished comments.
     *
     * @param $blogId
     * @param $filters
     *
     * @return array
     */
    public function getUnpublishedComments($blogId, $filters)
    {
        if (!isset($filters['hiddenFilters'])) {
            $filters['hiddenFilters'] = [];
        }
        //filter on current blog and post
        $filters['hiddenFilters'] = array_merge(
            $filters['hiddenFilters'],
            [
                'blog' => $blogId,
                'status' => false,
            ]);

        return $this->finder->search('Icap\BlogBundle\Entity\Comment', $filters);
    }

    /**
     * Get trusted users.
     *
     * @param Blog blog
     *
     * @return array
     */
    public function getTrustedUsers(Blog $blog)
    {
        return $this->memberRepo->getTrustedMember($blog);
    }

    /**
     * Get reported comments.
     *
     * @param $blogId
     * @param $filters
     *
     * @return array
     */
    public function getReportedComments($blogId, $filters)
    {
        if (!isset($filters['hiddenFilters'])) {
            $filters['hiddenFilters'] = [];
        }
        //filter on current blog and post
        $filters['hiddenFilters'] = array_merge(
            $filters['hiddenFilters'],
            [
                'blog' => $blogId,
                'reported' => 1,
            ]);

        return $this->finder->search('Icap\BlogBundle\Entity\Comment', $filters);
    }

    /**
     * Get comments.
     *
     * @param $blogId
     * @param $postId
     * @param $userId
     * @param $filters
     * @param $allowedToSeeOnly
     */
    public function getComments($blogId, $postId, $userId, $filters, $allowedToSeeOnly)
    {
        if (!isset($filters['hiddenFilters'])) {
            $filters['hiddenFilters'] = [];
        }
        //filter on current blog and post
        $filters['hiddenFilters'] = [
            'blog' => $blogId,
            'post' => $postId,
        ];

        //allow to see only published post, or post whose current user is the author
        if ($allowedToSeeOnly) {
            //anonymous only sees published
            if (null === $userId) {
                $options = [
                    'publishedOnly' => true,
                ];
            } else {
                $options = [
                    'allowedToSeeForUser' => $userId,
                ];
            }

            $filters['hiddenFilters'] = array_merge(
                $filters['hiddenFilters'],
                $options);
        }

        return $this->finder->search('Icap\BlogBundle\Entity\Comment', $filters);
    }

    /**
     * Create a post comment.
     *
     * @param bool $forcePublication
     *
     * @return Comment
     */
    public function createComment(Blog $blog, Post $post, Comment $comment, $forcePublication = false)
    {
        $published = false;
        if ($blog->isAutoPublishComment()
            || $forcePublication
            || (BlogOptions::COMMENT_MODERATION_PRIOR_ONCE === $blog->getOptions()->getCommentModerationMode()
                && null !== $comment->getAuthor()
                && count($this->memberRepo->getTrustedMember($blog, $comment->getAuthor())) >= 1)) {
            $published = true;
        }

        $comment
            ->setPost($post)
            ->setStatus($published ? Comment::STATUS_PUBLISHED : Comment::STATUS_UNPUBLISHED);

        if (null === $comment->getCreationDate()) {
            $comment->setCreationDate(new \DateTime());
        }

        $this->om->persist($comment);
        $this->om->flush();

        $this->trackingManager->dispatchCommentCreateEvent($post, $comment);

        if (null !== $comment->getAuthor()) {
            $this->trackingManager->updateResourceTracking($blog->getResourceNode(), $comment->getAuthor(), new \DateTime());
        }

        return $comment;
    }

    /**
     * Update a comment.
     *
     * @param $message
     *
     * @return Comment
     *
     * @throws
     */
    public function updateComment(Blog $blog, Comment $existingComment, $message)
    {
        $existingComment
            ->setMessage($message)
            ->setStatus($blog->isAutoPublishComment() ? Comment::STATUS_PUBLISHED : Comment::STATUS_UNPUBLISHED)
            ->setPublicationDate($blog->isAutoPublishComment() ? new \DateTime() : null);

        $this->om->flush();

        $unitOfWork = $this->om->getUnitOfWork();
        $unitOfWork->computeChangeSets();
        $changeSet = $unitOfWork->getEntityChangeSet($existingComment);

        $this->trackingManager->dispatchCommentUpdateEvent($existingComment->getPost(), $existingComment, $changeSet);

        return $existingComment;
    }

    /**
     * Publish a comment.
     *
     * @return Comment
     */
    public function publishComment(Blog $blog, Comment $existingComment)
    {
        $existingComment->publish();
        if (BlogOptions::COMMENT_MODERATION_PRIOR_ONCE === $blog->getOptions()->getCommentModerationMode()
            && null !== $existingComment->getAuthor()) {
            if (0 === count($this->memberRepo->getTrustedMember($blog, $existingComment->getAuthor()))) {
                $this->addTrustedMember($blog, $existingComment->getAuthor());
            }
        }
        $this->om->flush();

        $this->trackingManager->dispatchCommentPublishEvent($existingComment->getPost(), $existingComment);

        return $existingComment;
    }

    /**
     * Add a trusted member to the blog, can write comment without verification from a moderator.
     *
     * @return Member
     */
    private function addTrustedMember(Blog $blog, User $user)
    {
        $member = new Member();
        $member->setBlog($blog);
        $member->setUser($user);
        $member->setTrusted(true);

        $this->om->persist($member);
        $this->om->flush();

        return $member;
    }

    /**
     * Add a banned member to the blog, cannot write comment.
     *
     * @return Member
     */
    public function addBannedMember(Blog $blog, User $user)
    {
        $member = new Member();
        $member->setBlog(blog);
        $member->setUser($user);
        $member->setBanned(true);

        $this->om->persist($member);
        $this->om->flush();

        return $member;
    }

    /**
     * Report a comment.
     *
     * @param Comment $comment
     * @param User    $user
     *
     * @return Comment
     */
    public function reportComment(Blog $blog, Comment $existingComment)
    {
        $existingComment->setReported($existingComment->getReported() + 1);
        $this->om->flush();

        return $existingComment;
    }

    /**
     * unpublish a comment.
     *
     * @param Comment $comment
     * @param User    $user
     *
     * @return Comment
     */
    public function unpublishComment(Blog $blog, Comment $existingComment)
    {
        $existingComment->unpublish();
        $this->om->flush();

        $this->trackingManager->dispatchCommentPublishEvent($existingComment->getPost(), $existingComment);

        return $existingComment;
    }

    /**
     * Delete a comment.
     */
    public function deleteComment(Blog $blog, Comment $existingComment)
    {
        $this->om->remove($existingComment);
        $this->om->flush();
        $this->trackingManager->dispatchCommentDeleteEvent($existingComment->getPost(), $existingComment);

        return $existingComment->getId();
    }
}
