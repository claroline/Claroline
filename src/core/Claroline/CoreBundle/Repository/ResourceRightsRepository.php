<?php

namespace Claroline\CoreBundle\Repository;

use Doctrine\ORM\EntityRepository;
use Claroline\CoreBundle\Entity\Resource\AbstractResource;

class ResourceRightsRepository extends EntityRepository
{
    /**
     * Used by the ResourceVoter.
     *
     * @param type array $rights
     * @param type AbstractResource $resource
     *
     * @return ResourceRights;
     */
    public function getRights($roles, AbstractResource $resource)
    {
        if(count($roles) == 0){
            throw new \RuntimeException("The role array cannot be empty for the getRights method in the ResourceRightRepository");
        }

        $dql = "
            SELECT
                MAX (rrw.canView) as canView,
                MAX (rrw.canEdit) as canEdit,
                MAX (rrw.canOpen) as canOpen,
                MAX (rrw.canDelete) as canDelete,
                MAX (rrw.canCopy) as canCopy,
                MAX (rrw.canExport) as canExport

            FROM Claroline\CoreBundle\Entity\Workspace\ResourceRights rrw
            JOIN rrw.role role
            JOIN rrw.resource resource
            WHERE  ";

            $i=0;

            foreach($roles as $role){
                if($i!=0){
                    $dql.= " OR resource.id = {$resource->getId()} AND role.name LIKE '{$role}'";
                } else {
                    $dql.= " resource.id = {$resource->getId()} AND role.name LIKE '{$role}'";
                    $i++;
                }
            }

            //récupérer la liste des trucs que je peux créer...

       $query = $this->_em->createQuery($dql);

       return $query->getSingleResult();
    }

    public function getCreationRights($roles, AbstractResource $resource)
    {
        if(count($roles) == 0){
            throw new \RuntimeException("The role array cannot be empty for the getRights method in the ResourceRightRepository");
        }

        $dql = "
            SELECT DISTINCT type.name
            FROM Claroline\CoreBundle\Entity\Resource\ResourceType type
            JOIN type.rights right
            JOIN right.role role
            JOIN right.resource resource
            WHERE  ";

            $i=0;

            foreach($roles as $role){
                if($i!=0){
                    $dql.= " OR resource.id = {$resource->getId()} AND role.name LIKE '{$role}'";
                } else {
                    $dql.= " resource.id = {$resource->getId()} AND role.name LIKE '{$role}'";
                    $i++;
                }
            }

            //récupérer la liste des trucs que je peux créer...

       $query = $this->_em->createQuery($dql);

       return $query->getArrayResult();
    }
}