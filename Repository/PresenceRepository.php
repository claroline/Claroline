<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace FormaLibre\PresenceBundle\Repository;


use Doctrine\ORM\EntityRepository;

class PresenceRepository extends EntityRepository
{
    public function OrderByNumPeriod($session,$date) {
        
         $dql = '
            SELECT p
            FROM FormaLibre\PresenceBundle\Entity\Presence p
            JOIN p.period pp
            WHERE p.courseSession = (:session)
            AND p.date =(:date)
            ORDER BY pp.numPeriod ASC
        ';

        $query = $this->_em->createQuery($dql);
        $query->setParameter('session', $session);
        $query->setParameter('date', $date);

        return $query->getResult();
        
    }
    
    public function OrderByStudent($session,$date,$period) {
        
         $dql = '
            SELECT p
            FROM FormaLibre\PresenceBundle\Entity\Presence p
            JOIN p.userStudent u
            WHERE p.courseSession = (:session)
            AND p.date =(:date)
            AND p.period =(:period)
            ORDER BY u.lastName ASC
        ';

        $query = $this->_em->createQuery($dql);
        $query->setParameter('session', $session);
        $query->setParameter('date', $date);
        $query->setParameter('period', $period);

        return $query->getResult();   
    }
    
    public function findBySchoolYear($year)
    {
        $dql = '
            SELECT p
            FROM FormaLibre\PresenceBundle\Entity\Presence p
            JOIN p.period pe
            JOIN pe.schoolYearId s
            WHERE s.schoolYearName = :year
        ';
        
        $query = $this->_em->createQuery($dql);
        $query->setParameter('year', $year->getSchoolYearName());

        return $query->getResult();   
    }
    
}