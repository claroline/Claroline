<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Event;

use Symfony\Component\EventDispatcher\Event;
use Doctrine\ORM\QueryBuilder;

class UserEditSearchEvent extends Event
{
    private $qb;

    public function __construct(QueryBuilder $qb)
    {
        $this->qb = $qb;
    }

    public function getQueryBuilder()
    {
        return $this->qb;
    }
}
