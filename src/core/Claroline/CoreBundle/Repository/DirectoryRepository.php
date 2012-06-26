<?php

namespace Claroline\CoreBundle\Repository;

use Doctrine\ORM\EntityRepository;
use Claroline\CoreBundle\Entity\Resource\AbstractResource;
use Claroline\CoreBundle\Entity\Resource\Directory;

class DirectoryRepository extends EntityRepository
{
    public function getDirectoryDirectChildren(Directory $directory)
    {
        $dql = "
            SELECT r FROM Claroline\CoreBundle\Entity\Resource\Directory r
            WHERE r.parent = '{$directory->getId()}'
        ";

        $query = $this->_em->createQuery($dql);

        return $query->getResult();
    }

    public function getNotDirectoryDirectChildren(Directory $directory)
    {
        $dql = "
            SELECT r FROM Claroline\CoreBundle\Entity\Resource\AbstractResource r
            JOIN r.resourceType rt
            WHERE r.parent = '{$directory->getId()}'
            AND rt.type != 'directory'
        ";

        $query = $this->_em->createQuery($dql);

        return $query->getResult();
    }
}