<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CursusBundle\Repository;

use Claroline\CursusBundle\Entity\Quota;
use Doctrine\ORM\EntityRepository;

class QuotaRepository extends EntityRepository
{
    public function countValidated(Quota $quota)
    {
        return 123456789;
    }
}
