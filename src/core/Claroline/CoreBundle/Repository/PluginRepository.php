<?php

namespace Claroline\CoreBundle\Repository;

use Doctrine\ORM\EntityRepository;

class PluginRepository extends EntityRepository
{
    public function findOneByBundleFQCN($fqcn)
    {
        $split = explode('\\', $fqcn);
        $dql = "
            SELECT p FROM Claroline\CoreBundle\Entity\Plugin p
            WHERE p.vendorName = :vendor
            AND p.bundleName = :bundle";

        $query = $this->_em->createQuery($dql);
        $query->setParameter('vendor', $split[0]);
        $query->setParameter('bundle', $split[1]);

        $results = $query->getResult();

        return (count($results) !== 0) ? $results[0]: null;
    }
}
