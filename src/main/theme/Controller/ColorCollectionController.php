<?php

namespace Claroline\ThemeBundle\Controller;

use Claroline\AppBundle\Controller\AbstractCrudController;


/**
 * ColorCollectionController.
 */
class ColorCollectionController extends AbstractCrudController
{
    public function getName(): string
    {
        return 'color_collection';
    }
}
