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
    public function findMaximumRights(array $roles, AbstractResource $resource)
    {
        if (count($roles) === 0) {
            throw new \RuntimeException('Roles cannot be empty');
        }

        $dql = '
            SELECT
                MAX (rrw.canEdit) as canEdit,
                MAX (rrw.canOpen) as canOpen,
                MAX (rrw.canDelete) as canDelete,
                MAX (rrw.canCopy) as canCopy,
                MAX (rrw.canExport) as canExport
            FROM Claroline\CoreBundle\Entity\Resource\ResourceRights rrw
            JOIN rrw.role role
            JOIN rrw.resource resource
            WHERE ';

        $index = 0;

        foreach ($roles as $role) {
            $dql .= $index !== 0 ? ' OR ' : '';
            $dql .= "resource.id = {$resource->getId()} AND role.name LIKE '{$role}'";
            ++$index;
        }

        $query = $this->_em->createQuery($dql);

        return $query->getSingleResult();
    }

    public function findCreationRights(array $roles, AbstractResource $resource)
    {
        if (count($roles) === 0) {
            throw new \RuntimeException('Roles cannot be empty');
        }

        $dql = '
            SELECT DISTINCT type.name
            FROM Claroline\CoreBundle\Entity\Resource\ResourceType type
            JOIN type.rights right
            JOIN right.role role
            JOIN right.resource resource
            WHERE ';

        $index = 0;

        foreach ($roles as $role) {
            $dql .= $index !== 0 ? ' OR ' : '';
            $dql .= "resource.id = {$resource->getId()} AND role.name LIKE '{$role}'";
            ++$index;
        }

        $query = $this->_em->createQuery($dql);

        return $query->getArrayResult();
    }

    public function findNonAdminRights(AbstractResource $resource)
    {
        $dql = "
            SELECT rights
            FROM Claroline\CoreBundle\Entity\Resource\ResourceRights rights
            JOIN rights.resource resource
            JOIN rights.role role
            JOIN rights.workspace workspace
            WHERE resource.id = {$resource->getId()}
            AND role.name != 'ROLE_ADMIN'
            AND resource.workspace = workspace
            ORDER BY role.name
        ";

        return $this->_em->createQuery($dql)->getResult();
    }
}