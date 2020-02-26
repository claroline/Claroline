<?php

namespace Claroline\ThemeBundle\Controller;

use Claroline\AppBundle\Controller\AbstractCrudController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration as EXT;

/**
 * @EXT\Route("/color")
 */
class ColorCollectionController extends AbstractCrudController
{
    public function getName()
    {
        return 'color_collection';
    }
}
