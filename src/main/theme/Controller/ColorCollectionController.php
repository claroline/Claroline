<?php

namespace Claroline\ThemeBundle\Controller;

use Claroline\AppBundle\Controller\AbstractCrudController;
use Claroline\ThemeBundle\Entity\ColorCollection;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/color_collection", name="apiv2_color_collection_")
 */
class ColorCollectionController extends AbstractCrudController
{
    public static function getName(): string
    {
        return 'color_collection';
    }

    public static function getClass(): string
    {
        return ColorCollection::class;
    }
}
