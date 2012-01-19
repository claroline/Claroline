<?php

namespace Claroline\CoreBundle\Repository;

use Doctrine\ORM\EntityRepository;

class UserRepository extends EntityRepository
{
    public function getUsersByUsernameList(array $usernames)
    {
        $nameList = array_map(
            function($name)
            { 
                return "'{$name}'"; 
            }, 
            $usernames
        );
        $nameList = implode(', ', $nameList);
        $dql = "
            SELECT u FROM Claroline\CoreBundle\Entity\User u
            WHERE u.username IN ({$nameList})
            ORDER BY u.username
        ";
        $query = $this->_em->createQuery($dql);
        
        return $query->getResult();
    }
}