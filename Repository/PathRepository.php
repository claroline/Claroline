<?php

namespace Innova\PathBundle\Repository;

use Doctrine\ORM\EntityRepository;
use Claroline\CoreBundle\Entity\Workspace\AbstractWorkspace;
use Symfony\Component\Security\Core\User\UserInterface;

class PathRepository extends EntityRepository
{
    public function findAllByWorkspaceByUser(AbstractWorkspace $workspace, UserInterface $user)
    {
        $dql = "SELECT p FROM Innova\PathBundle\Entity\Path p LEFT JOIN p.resourceNode rn WHERE rn.workspace = :workspace AND rn.creator = :user ORDER BY rn.name ASC";

        $query = $this->_em->createQuery($dql)
                ->setParameter('workspace', $workspace)
                ->setParameter('user', $user);

        return $query->getResult();
    }

    public function findAllByWorkspaceByNotUser(AbstractWorkspace $workspace, UserInterface $user)
    {
        $dql = "SELECT p FROM Innova\PathBundle\Entity\Path p LEFT JOIN p.resourceNode rn WHERE rn.workspace = :workspace AND rn.creator != :user ORDER BY rn.name ASC";

        $query = $this->_em->createQuery($dql)
                ->setParameter('workspace', $workspace)
                ->setParameter('user', $user);
                
        return $query->getResult();
    }
}
