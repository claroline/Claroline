<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace FormaLibre\PresenceBundle\Repository;

use Doctrine\ORM\EntityRepository;

class PresenceRightsRepository extends EntityRepository
{
    public function findPresenceRightsByRolesAndValue(array $roles, $rightValue)
    {
        $dql = '
            SELECT pr
            FROM FormaLibre\PresenceBundle\Entity\PresenceRights pr
            WHERE pr.role in (:roles)
            AND BIT_AND(pr.mask, :value) =:value
            
        ';

        $query = $this->_em->createQuery($dql);
        $query->setParameter('value', $rightValue);
        $query->setParameter('roles', $roles);

        return $query->getResult();
    }
}
