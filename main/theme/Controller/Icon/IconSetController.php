<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\ThemeBundle\Controller\Icon;

use Claroline\AppBundle\Controller\AbstractCrudController;
use Claroline\ThemeBundle\Entity\Icon\IconSet;
use Sensio\Bundle\FrameworkExtraBundle\Configuration as EXT;

/**
 * @EXT\Route("/icon_set")
 */
class IconSetController extends AbstractCrudController
{
    public function getName()
    {
        return 'icon_set';
    }

    public function getClass()
    {
        return IconSet::class;
    }

    public function getIgnore()
    {
        return ['exist', 'copyBulk', 'schema', 'doc', 'find'];
    }
}
