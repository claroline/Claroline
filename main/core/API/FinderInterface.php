<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\API;

use Doctrine\ORM\QueryBuilder;

interface FinderInterface
{
    //the queried object is already named "obj".
    public function configureQueryBuilder(QueryBuilder $qb, array $searches, array $sortBy = null);
    public function getClass();
}
