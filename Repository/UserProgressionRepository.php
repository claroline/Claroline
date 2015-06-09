<?php

namespace Innova\PathBundle\Repository;

use Doctrine\ORM\EntityRepository;
use Claroline\CoreBundle\Entity\User;
use Innova\PathBundle\Entity\Path\Path;

class UserProgressionRepository extends EntityRepository
{
    public function findByPathAndUser(Path $path, User $user)
    {

    }
}