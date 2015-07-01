<?php

namespace Innova\PathBundle\Repository;

use Doctrine\ORM\EntityRepository;
use Claroline\CoreBundle\Entity\User;
use Innova\PathBundle\Entity\Path\Path;

class UserProgressionRepository extends EntityRepository
{
    /**
     * Get the progression of the User into the Path
     * @param Path $path
     * @param User $user
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

        $progression = array ();

        foreach ($results as $result) {
            $progression[$result->getStep()->getId()] = $result;
        }

        return $progression;
    }
}