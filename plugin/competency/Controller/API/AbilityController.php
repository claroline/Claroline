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
use HeVinci\CompetencyBundle\Entity\Ability;
use Sensio\Bundle\FrameworkExtraBundle\Configuration as EXT;

/**
 * @EXT\Route("/ability")
 */
class AbilityController extends AbstractCrudController
{
    public function getName()
    {
        return 'ability';
    }

    public function getClass()
    {
        return Ability::class;
    }

    public function getIgnore()
    {
        return ['create', 'update', 'deleteBulk', 'get', 'exist', 'copyBulk', 'schema', 'find'];
    }
}
