<?php

namespace Icap\BlogBundle\Manager;

use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Entity\User;
use Icap\BlogBundle\Repository\CommentRepository;
use JMS\DiExtraBundle\Annotation as DI;

/**
 * @DI\Service("icap.blog.manager.comment")
 */
class CommentManager
{
    /**
     * @var ObjectManager
     */
    protected $om;

    /** @var \Icap\BlogBundle\Repository\CommentRepository */
    protected $repo;

    /**
     * @DI\InjectParams({
     *     "om" = @DI\Inject("claroline.persistence.object_manager"),
     *     "repo" = @DI\Inject("icap.blog.comment_repository")
     * })
     *
     * @param ObjectManager     $om
     * @param CommentRepository $repo
     */
    public function __construct(ObjectManager $om, CommentRepository $repo)
    {
        $this->om = $om;
        $this->repo = $repo;
    }

    /**
     * Find all content for a given user and the replace him by another.
     *
     * @param User $from
     * @param User $to
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
}
