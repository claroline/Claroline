<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\ThemeBundle\Controller;

use Claroline\AppBundle\Controller\AbstractCrudController;
use Claroline\ThemeBundle\Entity\Theme;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/theme")
 */
class ThemeController extends AbstractCrudController
{
    public function getClass(): string
    {
        return Theme::class;
    }

    public function getName(): string
    {
        return 'theme';
    }
}
