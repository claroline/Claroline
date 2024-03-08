<?php

namespace Claroline\ThemeBundle\Controller;

use Claroline\AppBundle\Controller\AbstractCrudController;
use Claroline\ThemeBundle\Entity\Theme;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/theme")
 */
class ThemeController extends AbstractCrudController
{
    public function getName(): string
    {
        return 'theme';
    }

    public function getClass(): string
    {
        return Theme::class;
    }
}
