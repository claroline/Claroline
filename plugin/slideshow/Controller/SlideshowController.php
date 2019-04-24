<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\SlideshowBundle\Controller;

use Claroline\AppBundle\Controller\AbstractCrudController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration as EXT;

/**
 * @EXT\Route("/slideshow")
 */
class SlideshowController extends AbstractCrudController
{
    public function getClass()
    {
        return 'Claroline\SlideshowBundle\Entity\Resource\Slideshow';
    }

    public function getName()
    {
        return 'slideshow';
    }
}
