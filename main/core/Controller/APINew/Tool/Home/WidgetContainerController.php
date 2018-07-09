<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Controller\APINew\Tool\Home;

use Claroline\AppBundle\Controller\AbstractCrudController;
use Claroline\CoreBundle\Entity\Widget\WidgetContainer;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;

/**
 * @Route("/widget_container")
 */
class WidgetContainerController extends AbstractCrudController
{
    public function getName()
    {
        return 'widget_container';
    }

    public function getClass()
    {
        return WidgetContainer::class;
    }
}
