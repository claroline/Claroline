<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Controller\APINew;

use Claroline\AppBundle\Controller\AbstractCrudController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;

/**
 * @Route("/theme")
 */
class ThemeController extends AbstractCrudController
{
    public function getClass()
    {
        return 'Claroline\CoreBundle\Entity\Theme\Theme';
    }

    public function getName()
    {
        return 'theme';
    }
}
