<?php

namespace Innova\PathBundle\Repository;

use Claroline\CoreBundle\Entity\User;
use Doctrine\ORM\EntityRepository;
use Innova\PathBundle\Entity\Path\Path;

class UserProgressionRepository extends EntityRepository
{
    /**
     * Get the progression of the User into the Path.
     *
     * @param Path $path
     * @param User $user
     *
     * @return array
     */
    public function findByPathAndUser(Path $path, User $user)
    {
        $query = $this->createQueryBuilder('up')
            ->join('up.step', 's')

            // Progression of the current User
            ->where('up.user = :user')->setParameter('user', $user)

            // Only for the Steps of the needed Path
            ->andWhere('s.path = :path')->setParameter('path', $path)

            // Generate SQL query
            ->getQuery()
        ;

        // Get results of the query
        $results = $query->getResult();

        $progression = [];

        foreach ($results as $result) {
            $progression[$result->getStep()->getId()] = $result;
        }

        return $progression;
    }

    /**
     * Get total user progression in path.
     *
     * @param Path $path
     * @param User $user
     *
     * @return int
     */
    public function countProgressionForUserInPath(Path $path, User $user)
    {
        $qb = $this->createQueryBuilder('userProgression')
            ->select('COUNT(DISTINCT(userProgression.step)) AS total')
            ->leftJoin('userProgression.step', 'step')
            ->andWhere('userProgression.user = :user')
            ->andWhere('step.path = :path')
            ->andWhere('userProgression.status IN(:statuses)')
            ->setParameter('user', $user)
            ->setParameter('path', $path)
            ->setParameter('statuses', ['seen', 'done']);

        return intval($qb->getQuery()->getSingleScalarResult());
    }

    /**
     * @param User   $user
     * @param Path[] $paths
     *
     * @return int - the user progression in percents
     */
    public function findUserProgression(User $user, array $paths)
    {
        $result = $this
            ->getEntityManager()
            ->createQuery('
                SELECT 
                    COUNT(DISTINCT(up)) AS countDone,
                    COUNT(DISTINCT(s)) AS countTotal
                FROM Innova\PathBundle\Entity\Path\Path AS p
                JOIN Innova\PathBundle\Entity\Step AS s WITH s.path = p
                LEFT JOIN Innova\PathBundle\Entity\UserProgression AS up WITH up.step = s AND up.user = :user AND up.status IN (:statuses)
                WHERE p IN (:paths)
            ')
            ->setParameters([
                'user' => $user,
                'paths' => $paths,
                'statuses' => ['seen', 'done'],
            ])
            ->getSingleResult();

        if (0 < $result['countTotal']) {
            return round(($result['countDone'] / $result['countTotal']) * 100);
        } else {
            // No step in the path
            return 0;
        }
    }

    /**
     * Get all step called for unlock for a path.
     *
     * @param Path $path
     *
     * @return array
     */
    public function findByPathAndLockedStep(Path $path)
    {
        $query = $this->createQueryBuilder('up')
            ->join('up.step', 's')
            // Only for the Steps of the needed Path
            ->andWhere('s.path = :path')->setParameter('path', $path)
            ->andWhere('up.lockedcall = 1')
            // Generate SQL query
            ->getQuery()
        ;
        // Get results of the query
        $results = $query->getResult();

        return $results;
    }
}
