<?php

namespace FormaLibre\SupportBundle\Repository;

use Doctrine\ORM\EntityRepository;

class StatusRepository extends EntityRepository
{
    public function findStatusByType($type, $orderedBy = 'order', $order = 'ASC')
    {
        $dql = "
            SELECT s
            FROM FormaLibre\SupportBundle\Entity\Status s
            WHERE s.type = :type
            ORDER BY s.{$orderedBy} {$order}
        ";
        $query = $this->_em->createQuery($dql);
        $query->setParameter('type', $type);

        return $query->getResult();

    }
}
