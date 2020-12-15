<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\BigBlueButtonBundle\Finder;

use Claroline\AppBundle\API\Finder\AbstractFinder;
use Claroline\BigBlueButtonBundle\Entity\Recording;

class RecordingFinder extends AbstractFinder
{
    public function getClass()
    {
        return Recording::class;
    }
}
