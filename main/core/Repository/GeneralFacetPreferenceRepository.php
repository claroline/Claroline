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

class GeneralFacetPreferenceRepository extends EntityRepository
{
    public function getAdminPublicProfilePreferenceByRole(array $roles)
    {
        if (in_array('ROLE_ADMIN', $roles)) {
            return array(
                'baseData' => true,
                'mail' => true,
                'phone' => true,
                'sendMail' => true,
                'sendMessage' => true,
            );
        }

        $dql = "SELECT
            MAX(p.baseData) as baseData,
            MAX(p.mail) as mail,
            MAX(p.phone) as phone,
            MAX(p.mail) as sendMail,
            MAX(p.sendMessage) as sendMessage
            FROM Claroline\CoreBundle\Entity\Facet\GeneralFacetPreference p
            JOIN p.role as role
            WHERE role.name in (:rolenames)
        ";

        $query = $this->_em->createQuery($dql);
        $query->setParameter('rolenames', $roles);

        return $query->getSingleResult();
    }
}
