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
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/slideshow")
 */
class SlideshowController extends AbstractCrudController
{
    public function getClass(): string
    {
        return 'Claroline\SlideshowBundle\Entity\Resource\Slideshow';
    }

    public function getName(): string
    {
        return 'slideshow';
    }
}
