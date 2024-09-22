<?php

namespace Claroline\ThemeBundle\Controller;

use Claroline\AppBundle\Controller\AbstractCrudController;
use Claroline\ThemeBundle\Entity\Theme;
use Symfony\Component\Routing\Annotation\Route;

#[Route(path: '/theme', name: 'apiv2_theme_')]
class ThemeController extends AbstractCrudController
{
    public static function getName(): string
    {
        return 'theme';
    }

    public static function getClass(): string
    {
        return Theme::class;
    }
}
