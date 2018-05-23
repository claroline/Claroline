<?php

namespace Innova\CollecticielBundle\Manager;

use Claroline\AppBundle\Persistence\ObjectManager;
use Innova\CollecticielBundle\Repository\NotationRepository;
use JMS\DiExtraBundle\Annotation as DI;

/**
 * @DI\Service("innova.manager.notation_manager")
 */
class NotationManager
{
    /** @var ObjectManager */
    private $om;

    /** @var NotationRepository */
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
        $this->repo = $om->getRepository('InnovaCollecticielBundle:Notation');
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
        $notations = $this->repo->findByUser($from);

        if (count($notations) > 0) {
            foreach ($notations as $notation) {
                $notation->setUser($to);
            }

            $this->om->flush();
        }

        return count($notations);
    }
}
