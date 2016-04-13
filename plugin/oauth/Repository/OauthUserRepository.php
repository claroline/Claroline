<?php

namespace Icap\OAuthBundle\Repository;

use Doctrine\ORM\EntityRepository;

/**
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * Author: Panagiotis TSAVDARIS
 *
 * Date: 8/25/15
 */
class OauthUserRepository extends EntityRepository
{
    public function unlinkOAuthUser($userId)
    {
        $qb = $this->createQueryBuilder('oauth');
        $qb
            ->delete()
            ->andWhere('oauth.user = :user')
            ->setParameter('user', $userId);

        $qb->getQuery()->execute();
    }
}
