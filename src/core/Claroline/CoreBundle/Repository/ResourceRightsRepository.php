<?php

namespace Claroline\CoreBundle\Repository;

use Doctrine\ORM\EntityRepository;
use Claroline\CoreBundle\Entity\Resource\AbstractResource;
use Claroline\CoreBundle\Entity\Resource\Directory;
use Claroline\CoreBundle\Entity\Role;

class ResourceRightsRepository extends EntityRepository
{
    /**
     * Returns the maximum rights on a given resource for a set of roles.
     * Used by the ResourceVoter.
     *
     * @param array[string]    $rights
     * @param AbstractResource $resource
     *
     * @return array
     */
    public function findMaximumRights(array $roles, AbstractResource $resource)
    {
        if (count($roles) === 0) {
            throw new \RuntimeException('Roles cannot be empty');
        }

        $dql = '
            SELECT
                MAX (CASE rrw.canEdit WHEN true THEN 1 ELSE 0 END) as canEdit,
                MAX (CASE rrw.canOpen WHEN true THEN 1 ELSE 0 END) as canOpen,
                MAX (CASE rrw.canDelete WHEN true THEN 1 ELSE 0 END) as canDelete,
                MAX (CASE rrw.canCopy WHEN true THEN 1 ELSE 0 END) as canCopy,
                MAX (CASE rrw.canExport WHEN true THEN 1 ELSE 0 END) as canExport
            FROM Claroline\CoreBundle\Entity\Resource\ResourceRights rrw
            JOIN rrw.role role
            JOIN rrw.resource resource
            WHERE ';

        $index = 0;

        foreach ($roles as $role) {
            $dql .= $index !== 0 ? ' OR ' : '';
            $dql .= "resource.id = {$resource->getId()} AND role.name = '{$role}'";
            ++$index;
        }

        $query = $this->_em->createQuery($dql);

        return $query->getSingleResult();
    }

    /**
     * Returns the resource types a set of roles is allowed to create in a given
     * directory.
     *
     * @param array            $roles
     * @param AbstractResource $resource
     *
     * @return array
     */
    public function findCreationRights(array $roles, Directory $directory)
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
            $dql .= "resource.id = {$directory->getId()} AND role.name = '{$role}'";
            ++$index;
        }

        $query = $this->_em->createQuery($dql);

        return $query->getArrayResult();
    }

    /**
     * @todo to be removed
     */
    public function findNonAdminRights(AbstractResource $resource)
    {
        $dql = "
            SELECT rights
            FROM Claroline\CoreBundle\Entity\Resource\ResourceRights rights
            JOIN rights.resource resource
            JOIN rights.role role
            WHERE resource.id = {$resource->getId()}
            AND role.name != 'ROLE_ADMIN'
            ORDER BY role.name
        ";

        return $this->_em->createQuery($dql)->getResult();
    }

    /**
     * Returns all the resource rights of a resource and its descendants.
     *
     * @param AbstractResource $resource
     *
     * @return array[ResourceRights]
     */
    public function findRecursiveByResource(AbstractResource $resource)
    {
        $dql = "
            SELECT rights, role, resource
            FROM Claroline\CoreBundle\Entity\Resource\ResourceRights rights
            JOIN rights.resource resource
            JOIN rights.role role
            WHERE resource.path LIKE '{$resource->getPath()}%'
        ";

        return $this->_em->createQuery($dql)->getResult();
    }

    /**
     * Find ResourceRights for each descendant of a resource for a role.
     *
     * @param \Claroline\CoreBundle\Entity\Resource\AbstractResource $resource
     * @param \Claroline\CoreBundle\Entity\Role                      $role
     *
     * @return array
     */
    public function findRecursiveByResourceAndRole(AbstractResource $resource, Role $role)
    {
        $dql = "
            SELECT rights, role, resource
            FROM Claroline\CoreBundle\Entity\Resource\ResourceRights rights
            JOIN rights.resource resource
            JOIN rights.role role
            WHERE resource.path LIKE '{$resource->getPath()}%' AND role.name = '{$role->getName()}'
        ";

        return $this->_em->createQuery($dql)->getResult();
    }
}
