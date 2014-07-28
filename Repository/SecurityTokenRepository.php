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

class SecurityTokenRepository extends EntityRepository
{
    public function findAllTokens($order = 'tokenName', $direction = 'ASC')
    {
        $dql = "
            SELECT st
            FROM Claroline\CoreBundle\Entity\SecurityToken st
            ORDER BY st.{$order} {$direction}
        ";

        $query = $this->_em->createQuery($dql);
        return $query->getResult();
    }
}
