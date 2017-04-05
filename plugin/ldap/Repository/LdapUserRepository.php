<?php
/**
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * Date: 3/30/17
 */

namespace Claroline\LdapBundle\Repository;

use Doctrine\ORM\EntityRepository;

class LdapUserRepository extends EntityRepository
{
    public function deleteUsersByServerName($serverName)
    {
        $qb = $this->createQueryBuilder('ldapUser')
            ->delete()
            ->andWhere('ldapUser.serverName = :serverName')
            ->setParameter('serverName', $serverName);

        return $qb->getQuery()->execute();
    }

    public function updateUsersByServerName($oldServerName, $newServerName)
    {
        $qb = $this->createQueryBuilder('ldapUser')
            ->update()
            ->set('ldapUser.serverName', '?1')
            ->andWhere('ldapUser.serverName = ?2')
            ->setParameter(1, $newServerName)
            ->setParameter(2, $oldServerName);

        return $qb->getQuery()->execute();
    }

    public function unlinkLdapUser($userId)
    {
        $qb = $this->createQueryBuilder('ldapUser');
        $qb
            ->delete()
            ->andWhere('ldapUser.user = :user')
            ->setParameter('user', $userId);

        return $qb->getQuery()->execute();
    }
}
