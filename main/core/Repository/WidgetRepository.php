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

class WidgetRepository extends EntityRepository
{
    /**
     * @return \Claroline\CoreBundle\Entity\Tool\Tool|]
     */
    public function findAllWithPlugin()
    {
        return $this->createQueryBuilder('widget')
            ->leftJoin('widget.plugin', 'plugin')
            ->getQuery()
            ->getResult();
    }
}
