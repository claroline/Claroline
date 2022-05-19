<?php

namespace HeVinci\CompetencyBundle\Repository;

use Claroline\CoreBundle\Entity\Group;
use Claroline\CoreBundle\Entity\Resource\ResourceNode;
use Claroline\CoreBundle\Entity\User;
use Gedmo\Tree\Entity\Repository\NestedTreeRepository;
use HeVinci\CompetencyBundle\Entity\Competency;

class CompetencyRepository extends NestedTreeRepository
{
    /**
     * Returns the competency roots by name (used by validator).
     *
     * @param string $name
     *
     * @return array
     */
    public function findRootsByName($name)
    {
        return $this->findBy(['name' => $name, 'parent' => null]);
    }

    /**
     * Returns the competencies associated with a resource.
     *
     * @return array
     */
    public function findByResource(ResourceNode $resource)
    {
        return $this->createQueryBuilder('c')
            ->select('c')
            ->join('c.resources', 'r')
            ->where('r = :resource')
            ->setParameter(':resource', $resource)
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
     *
     * @return array
     */
    public function findFirstUsersByName($search)
    {
        return $this->_em->createQueryBuilder()
            ->select(
                'u.id',
                "CONCAT(u.firstName, ' ', u.lastName, ' (', u.username, ')') AS name"
            )
            ->from(User::class, 'u')
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
     *
     * @return array
     */
    public function findFirstGroupsByName($search)
    {
        return $this->_em->createQueryBuilder()
            ->select('g.id, g.name')
            ->from(Group::class, 'g')
            ->where('g.name LIKE :search')
            ->setMaxResults(5)
            ->setParameter(':search', "%{$search}%")
            ->getQuery()
            ->getArrayResult();
    }

    /**
     * Returns all the competency nodes that must be taken into
     * account when computing level/percentage of parent nodes
     * from a given start node. It includes the node's siblings
     * and parent, and the parent's siblings and parent, and so on.
     *
     * @return array
     */
    public function findForProgressComputing(Competency $startNode)
    {
        $parent = $startNode->getParent();
        if (!$parent) {
            return [];
        }

        $qb = $this->createQueryBuilder('c');

        return $qb
            ->select('c')
            ->where('c != :startNode')
            ->andWhere('c.root = :root')
            ->andWhere($qb->expr()->orX(
                $qb->expr()->andX(
                    'c.lft > :parentLft',
                    'c.rgt < :parentRgt',
                    'c.lvl = :childLvl'
                ),
                'c.lvl < :childLvl'
            ))
            ->setParameters([
                ':startNode' => $startNode,
                ':root' => $parent->getRoot(),
                ':childLvl' => $parent->getLevel() + 1,
                ':parentLft' => $parent->getLeft(),
                ':parentRgt' => $parent->getRight(),
            ])
            ->getQuery()
            ->getResult();
    }
}
