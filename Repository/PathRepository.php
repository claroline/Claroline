<?php

namespace Innova\PathBundle\Repository;

use Claroline\CoreBundle\Entity\Resource\ResourceNode;
use Doctrine\ORM\EntityRepository;

class PathRepository extends EntityRepository
{

    public function findAllByWorkspace(AbstractWorkspace $workspace)
    {
    	
       

    }
}
