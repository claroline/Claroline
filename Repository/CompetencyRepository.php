<?php

namespace HeVinci\CompetencyBundle\Repository;

use Claroline\CoreBundle\Entity\Resource\Activity;
use Gedmo\Tree\Entity\Repository\NestedTreeRepository;

class CompetencyRepository extends NestedTreeRepository
{
    /**
     * Returns the competency roots by name (used by validator).
     *
     * @param string $name
     * @return array
     */
    public function findRootsByName($name)
    {
        return $this->findBy(['name' => $name, 'parent' => null]);
    }

    /**
     * Returns the competencies associated with an activity.
     *
     * @param Activity $activity
     * @return array
     */
    public function findByActivity(Activity $activity)
    {
        return $this->createQueryBuilder('c')
            ->select('c')
            ->join('c.activities', 'a')
            ->where('a = :activity')
            ->setParameter(':activity', $activity)
            ->getQuery()
            ->getResult();
    }
}
