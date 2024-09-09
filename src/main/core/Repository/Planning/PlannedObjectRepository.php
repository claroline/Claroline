<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Repository\Planning;

use Claroline\AppBundle\Repository\UniqueValueFinder;
use Doctrine\ORM\EntityRepository;

class PlannedObjectRepository extends EntityRepository
{
    use UniqueValueFinder;
}
