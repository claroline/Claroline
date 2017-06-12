<?php

namespace FormaLibre\SupportBundle\Repository;

use Doctrine\ORM\EntityRepository;

class StatusRepository extends EntityRepository
{
    public function findAllStatus($orderedBy = 'order', $order = 'ASC')
    {
        $dql = "
            SELECT s
            FROM FormaLibre\SupportBundle\Entity\Status s
            ORDER BY s.{$orderedBy} {$order}
        ";
        $query = $this->_em->createQuery($dql);

        return $query->getResult();
    }

    public function findAllSearchedStatus($search, $orderedBy = 'order', $order = 'ASC')
    {
        $dql = "
            SELECT s
            FROM FormaLibre\SupportBundle\Entity\Status s
            WHERE UPPER(s.name) LIKE :search
            OR UPPER(s.code) LIKE :search
            ORDER BY t.{$orderedBy} {$order}
        ";
        $query = $this->_em->createQuery($dql);
        $upperSearch = strtoupper($search);
        $query->setParameter('search', "%{$upperSearch}%");

        return $query->getResult();
    }

    public function findOrderOfLastStatus()
    {
        $dql = '
            SELECT MAX(s.order) AS order_max
            FROM FormaLibre\SupportBundle\Entity\Status s
        ';
        $query = $this->_em->createQuery($dql);

        return $query->getSingleResult();
    }

    public function findStatusByCodeInsensitive($code)
    {
        $dql = '
            SELECT s
            FROM FormaLibre\SupportBundle\Entity\Status s
            WHERE UPPER(s.code) = :code
        ';
        $query = $this->_em->createQuery($dql);
        $query->setParameter('code', strtoupper($code));

        return $query->getOneOrNullResult();
    }
}
