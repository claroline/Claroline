<?php

namespace Claroline\CoreBundle\Repository;

use Doctrine\ORM\EntityRepository;
use Claroline\CoreBundle\Entity\Resource\AbstractResource;
use Gedmo\Tree\Entity\Repository\NestedTreeRepository;

class ResourceInstanceRepository extends EntityRepository
{
    public function getPersonnalWSListableRootResource($ws)
    {
        $dql = "
            SELECT re FROM Claroline\CoreBundle\Entity\Resource\ResourceInstance re
            WHERE re.lvl = 0
            AND re.workspace = {$ws->getId()}
            ";
            
        $query = $this->_em->createQuery($dql);
        
        return $query->getResult();
    }
}