<?php

namespace Innova\PathBundle\Repository;

use Doctrine\ORM\EntityRepository;
use Claroline\CoreBundle\Entity\Workspace\AbstractWorkspace;
use Symfony\Component\Security\Core\User\UserInterface;

class PathRepository extends EntityRepository
{
    public function findAllByWorkspaceByUser(AbstractWorkspace $workspace, UserInterface $user)
    {
        $dql  = 'SELECT p ';
        $dql .= 'FROM Innova\PathBundle\Entity\Path\Path p ';
        $dql .= 'LEFT JOIN p.resourceNode rn ';
        $dql .= 'WHERE rn.workspace = :workspace ';
        $dql .= '  AND rn.creator = :user ';
        $dql .= 'ORDER BY rn.name ASC ';
        
        $query = $this->_em->createQuery($dql);
        
        $query->setParameter('workspace', $workspace);
        $query->setParameter('user', $user);

        return $query->getResult();
    }

    public function findAllByWorkspaceByNotUser(AbstractWorkspace $workspace, UserInterface $user)
    {
        $dql  = 'SELECT p ';
        $dql .= 'FROM Innova\PathBundle\Entity\Path\Path p ';
        $dql .= 'LEFT JOIN p.resourceNode rn ';
        $dql .= 'WHERE rn.workspace = :workspace ';
        $dql .= '  AND rn.creator != :user ';
        $dql .= 'ORDER BY rn.name ASC';
        
        $query = $this->_em->createQuery($dql);
        
        $query->setParameter('workspace', $workspace);
        $query->setParameter('user', $user);

        return $query->getResult();
    }

    public function findAllByWorkspace(AbstractWorkspace $workspace)
    {
        $dql  = 'SELECT p ';
        $dql .= 'FROM Innova\PathBundle\Entity\Path\Path p ';
        $dql .= 'LEFT JOIN p.resourceNode rn ';
        $dql .= 'WHERE rn.workspace = :workspace ';
        $dql .= 'ORDER BY rn.name ASC';
        
        $query = $this->_em->createQuery($dql);
        
        $query->setParameter('workspace', $workspace);

        return $query->getResult();
    }
}