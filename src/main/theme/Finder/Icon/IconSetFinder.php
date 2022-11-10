<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\ThemeBundle\Finder\Icon;

use Claroline\AppBundle\API\Finder\AbstractFinder;
use Claroline\ThemeBundle\Entity\Icon\IconSet;

class IconSetFinder extends AbstractFinder
{
    public static function getClass(): string
    {
        return IconSet::class;
    }
}
