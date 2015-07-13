<?php

namespace FormaLibre\SupportBundle\Repository;

use Doctrine\ORM\EntityRepository;

class TypeRepository extends EntityRepository
{
    public function findAllTypes($orderedBy = 'name', $order = 'ASC')
    {
        $dql = "
            SELECT t
            FROM FormaLibre\SupportBundle\Entity\Type t
            ORDER BY t.{$orderedBy} {$order}
        ";
        $query = $this->_em->createQuery($dql);

        return $query->getResult();
    }

    public function findAllSearchedTypes($search, $orderedBy = 'name', $order = 'ASC')
    {
        $dql = "
            SELECT t
            FROM FormaLibre\SupportBundle\Entity\Type t
            WHERE UPPER(t.name) LIKE :search
            ORDER BY t.{$orderedBy} {$order}
        ";
        $query = $this->_em->createQuery($dql);
        $upperSearch = strtoupper($search);
        $query->setParameter('search', "%{$upperSearch}%");

        return $query->getResult();
    }
}
