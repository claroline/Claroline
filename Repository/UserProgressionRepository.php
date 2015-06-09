<?php

namespace Innova\PathBundle\Repository;

use Doctrine\ORM\EntityRepository;
use Claroline\CoreBundle\Entity\User;
use Innova\PathBundle\Entity\Path\Path;

class UserProgressionRepository extends EntityRepository
{
    public function findByPathAndUser(Path $path, User $user)
    {
        return $this->createQueryBuilder('up')
            ->join('up.step', 's')

            // Progression of the current User
            ->where('up.user = :user')->setParameter('user', $user)

            // Only for the Steps of the needed Path
            ->andWhere('s.path = :path')->setParameter('path', $path)
        ;
    }
}