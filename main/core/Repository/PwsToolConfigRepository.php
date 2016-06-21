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
use Claroline\CoreBundle\Entity\User;

class PwsToolConfigRepository extends EntityRepository
{
    /**
     * Returns the configuration for a user and it's rights (masks already calculated).
     *
     * @param array $roleNames
     *
     * @return array
     */
    public function findByRoles(array $roleNames)
    {
        $dql = 'SELECT pws from Claroline\CoreBundle\Entity\Tool\PwsToolConfig pws
            JOIN pws.role role
            WHERE role.name IN (:roleNames)
        ';

        $query = $this->_em->createQuery($dql);
        $query->setParameter('roleNames', $roleNames);
        $pwsToolConfigs = $query->getResult();

        $data = [];

        foreach ($pwsToolConfigs as $pwsToolConfig) {
            $data[$pwsToolConfig->getTool()->getId()]['toolConfig'] = $pwsToolConfig;
            if (!isset($data[$pwsToolConfig->getTool()->getId()]['mask'])) {
                $data[$pwsToolConfig->getTool()->getId()]['mask'] = 0;
            }
            $data[$pwsToolConfig->getTool()->getId()]['mask'] |= $pwsToolConfig->getMask();
        }

        return $data;
    }
}
