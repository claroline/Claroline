<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace HeVinci\CompetencyBundle\Controller\API;

use Claroline\AppBundle\Controller\AbstractCrudController;
use HeVinci\CompetencyBundle\Entity\Scale;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/competency_scale")
 */
class ScaleController extends AbstractCrudController
{
    public function getName()
    {
        return 'scale';
    }

    public function getClass()
    {
        return Scale::class;
    }

    public function getIgnore()
    {
        return ['exist', 'copyBulk', 'schema', 'find'];
    }
}
