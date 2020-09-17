<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\DropZoneBundle\Controller\API;

use Claroline\AppBundle\Controller\AbstractCrudController;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/dropzonetool")
 */
class DropzoneToolController extends AbstractCrudController
{
    public function getClass()
    {
        return 'Claroline\DropZoneBundle\Entity\DropzoneTool';
    }

    public function getName()
    {
        return 'dropzone_tool';
    }
}
