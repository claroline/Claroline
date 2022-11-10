<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace HeVinci\CompetencyBundle\Finder;

use Claroline\AppBundle\API\Finder\AbstractFinder;
use HeVinci\CompetencyBundle\Entity\Scale;

class ScaleFinder extends AbstractFinder
{
    public static function getClass(): string
    {
        return Scale::class;
    }
}
