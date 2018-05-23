<?php

namespace Claroline\ResultBundle\Manager;

use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Entity\User;
use Claroline\ResultBundle\Repository\MarkRepository;
use JMS\DiExtraBundle\Annotation as DI;

/**
 * @DI\Service("claroline.result.mark_manager")
 */
class MarkManager
{
    /** @var ObjectManager */
    private $om;

    /** @var MarkRepository */
    private $repo;

    /**
     * @DI\InjectParams({
     *      "om" = @DI\Inject("claroline.persistence.object_manager")
     * })
     *
     * @param ObjectManager $om
     */
    public function __construct(ObjectManager $om)
    {
        $this->om = $om;
        $this->repo = $om->getRepository('ClarolineResultBundle:Mark');
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
        $marks = $this->repo->findByUser($from);

        if (count($marks) > 0) {
            foreach ($marks as $mark) {
                $mark->setUser($to);
            }

            $this->om->flush();
        }

        return count($marks);
    }
}
