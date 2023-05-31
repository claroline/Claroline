<?php

namespace Claroline\ThemeBundle\Controller;

use Claroline\AppBundle\Controller\AbstractCrudController;
use Claroline\ThemeBundle\Entity\ColorCollection;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/color_collection")
 */
class ColorCollectionController extends AbstractCrudController
{
    public function getName(): string
    {
        return 'color_collection';
    }

    public function getClass(): string
    {
        return ColorCollection::class;
    }
}
