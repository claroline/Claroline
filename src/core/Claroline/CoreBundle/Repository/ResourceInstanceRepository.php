<?php

namespace Claroline\CoreBundle\Repository;

use Doctrine\ORM\EntityRepository;
use Claroline\CoreBundle\Entity\Resource\AbstractResource;
use Gedmo\Tree\Entity\Repository\NestedTreeRepository;

class ResourceInstanceRepository extends EntityRepository
{
    public function getResourceInstanceFromRepository($repository)
    {
        $dql = "
            SELECT r FROM Claroline\CoreBundle\Entity\Resource\ResourceInstance r
            JOIN r.repository repo WHERE repo.id = {$repository->getId()}
            ";
            
        $query = $this->_em->createQuery($dql);
            
        return $query->getResult(); 
    }
}