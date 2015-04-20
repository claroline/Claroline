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

    /**
     * Returns the first five users whose first name, last name or
     * username include a given string.
     *
     * Note: this should definitely not be here
     *
     * @param string $search
     * @return array
     */
    public function findFirstUsersByName($search)
    {
        return $this->_em->createQueryBuilder()
            ->select(
                'u.id',
                "CONCAT(u.firstName, ' ', u.lastName, ' (', u.username, ')') AS name"
            )
            ->from('ClarolineCoreBundle:User', 'u')
            ->where('u.firstName LIKE :search')
            ->orWhere('u.lastName LIKE :search')
            ->orWhere('u.username LIKE :search')
            ->setMaxResults(5)
            ->setParameter(':search', "%{$search}%")
            ->getQuery()
            ->getArrayResult();
    }

    /**
     * Returns the first five users whose name includes a given string.
     *
     * Note: this should definitely not be here
     *
     * @param string $search
     * @return array
     */
    public function findFirstGroupsByName($search)
    {
        return $this->_em->createQueryBuilder()
            ->select('g.id, g.name')
            ->from('ClarolineCoreBundle:Group', 'g')
            ->where('g.name LIKE :search')
            ->setMaxResults(5)
            ->setParameter(':search', "%{$search}%")
            ->getQuery()
            ->getArrayResult();
    }
}
