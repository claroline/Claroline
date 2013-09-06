<?php

namespace Claroline\CoreBundle\Repository;

use Doctrine\ORM\EntityRepository;
use Claroline\CoreBundle\Entity\Resource\ResourceNode;
use Claroline\CoreBundle\Entity\Role;

class ResourceRightsRepository extends EntityRepository
{
    /**
     * Returns the maximum rights on a given resource for a set of roles.
     * Used by the ResourceVoter.
     *
     * @param array[string] $rights
     * @param ResourceNode  $resource
     *
     * @return array
     */
    public function findMaximumRights(array $roles, ResourceNode $resource)
    {
        if (count($roles) === 0) {
            throw new \RuntimeException('Roles cannot be empty');
        }

        $dql = '
            SELECT rrw.mask
            FROM Claroline\CoreBundle\Entity\Resource\ResourceRights rrw
            JOIN rrw.role role
            JOIN rrw.resourceNode resource
            WHERE ';

        $index = 0;

        foreach ($roles as $role) {
            $dql .= $index !== 0 ? ' OR ' : '';
            $dql .= "resource.id = {$resource->getId()} AND role.name = '{$role}'";
            ++$index;
        }

        $query = $this->_em->createQuery($dql);
        $results = $query->getResult();
        $mask = 0;

        foreach ($results as $result) {
            $mask |= $result['mask'];
        }

        return $mask;

    }

    /**
     * Returns the resource types a set of roles is allowed to create in a given
     * directory.
     *
     * @param array        $roles
     * @param ResourceNode $resource
     *
     * @return array
     */
    public function findCreationRights(array $roles, ResourceNode $node)
    {
        if (count($roles) === 0) {
            throw new \RuntimeException('Roles cannot be empty');
        }

        $dql = '
            SELECT DISTINCT type.name
            FROM Claroline\CoreBundle\Entity\Resource\ResourceType type
            JOIN type.rights right
            JOIN right.role role
            JOIN right.resourceNode resource
            WHERE ';

        $index = 0;

        foreach ($roles as $role) {
            $dql .= $index !== 0 ? ' OR ' : '';
            $dql .= "resource.id = {$node->getId()} AND role.name = '{$role}'";
            ++$index;
        }

        $query = $this->_em->createQuery($dql);

        return $query->getArrayResult();
    }

    /**
     * @todo to be removed
     */
    public function findNonAdminRights(ResourceNode $resource)
    {
        $dql = "
            SELECT rights
            FROM Claroline\CoreBundle\Entity\Resource\ResourceRights rights
            JOIN rights.resourceNode resource
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
     * @param ResourceNode $resource
     *
     * @return array[ResourceRights]
     */
    public function findRecursiveByResource(ResourceNode $resource)
    {
        $dql = "
            SELECT rights, role, resource
            FROM Claroline\CoreBundle\Entity\Resource\ResourceRights rights
            JOIN rights.resourceNode resource
            JOIN rights.role role
            WHERE resource.path LIKE '{$resource->getPath()}%'
        ";

        return $this->_em->createQuery($dql)->getResult();
    }

    /**
     * Find ResourceRights for each descendant of a resource for a role.
     *
     * @param \Claroline\CoreBundle\Entity\Resource\ResourceNode $resource
     * @param \Claroline\CoreBundle\Entity\Role                  $role
     *
     * @return array
     */
    public function findRecursiveByResourceAndRole(ResourceNode $resource, Role $role)
    {
        $dql = "
            SELECT rights, role, resource
            FROM Claroline\CoreBundle\Entity\Resource\ResourceRights rights
            JOIN rights.resourceNode resource
            JOIN rights.role role
            WHERE resource.path LIKE '{$resource->getPath()}%' AND role.name = '{$role->getName()}'
        ";

        return $this->_em->createQuery($dql)->getResult();
    }
}
