<?php

namespace Innova\CollecticielBundle\Manager;

use Claroline\AppBundle\Persistence\ObjectManager;
use Innova\CollecticielBundle\Repository\CommentRepository;
use JMS\DiExtraBundle\Annotation as DI;

/**
 * @DI\Service("innova.manager.comment_manager")
 */
class CommentManager
{
    /** @var ObjectManager */
    private $om;

    /** @var CommentRepository */
    private $repo;

    /**
     * @DI\InjectParams({
     *     "om" = @DI\Inject("claroline.persistence.object_manager")
     * })
     *
     * @param ObjectManager $om
     */
    public function __construct(ObjectManager $om)
    {
        $this->om = $om;
        $this->repo = $om->getRepository('InnovaCollecticielBundle:Comment');
    }

    /**
     * Find all content for a given user and the replace him by another.
     *
     * @param User $from
     * @param User $to
     *
     * @return int
     */
    public function replaceUser(User $from, User $to)
    {
        $comments = $this->repo->findByUser($from);

        if (count($comments) > 0) {
            foreach ($comments as $comment) {
                $comment->setUser($to);
            }

            $this->om->flush();
        }

        return count($comments);
    }
}
