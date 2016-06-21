<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Repository;

use Doctrine\ORM\EntityRepository;

class ProfilePropertyRepository extends EntityRepository
{
    /**
     * Returns the accesses for a list of roles.
     */
    public function findAccessesByRoles(array $roles)
    {
        if (in_array('ROLE_ADMIN', $roles)) {
            return [
                'administrativeCode' => true,
                'description' => true,
                'email' => true,
                'firstName' => true,
                'lastName' => true,
                'phone' => true,
                'picture' => true,
                'username' => true,
            ];
        }

        $dql = '
            SELECT pp.property as property, MAX(pp.isEditable) as isEditable
            FROM Claroline\CoreBundle\Entity\ProfileProperty pp
            JOIN pp.role role
            WHERE role.name in (:roleNames)
            GROUP BY pp.property
        ';

        $query = $this->_em->createQuery($dql);
        $query->setParameter('roleNames', $roles);

        $results = $query->getResult();
        $properties = array();

        foreach ($results as $result) {
            $properties[$result['property']] = (boolean) $result['isEditable'];
        }

        return $properties;
    }
}
