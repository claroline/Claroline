<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\ClacoFormBundle\Controller\API;

use Claroline\AppBundle\Controller\AbstractCrudController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration as EXT;

/**
 * @EXT\Route("/clacoformentryuser")
 */
class EntryUserController extends AbstractCrudController
{
    public function getClass()
    {
        return 'Claroline\ClacoFormBundle\Entity\EntryUser';
    }

    public function getIgnore()
    {
        return ['exist', 'copyBulk', 'schema', 'find', 'list', 'create', 'deleteBulk', 'get'];
    }

    public function getName()
    {
        return 'clacoformentryuser';
    }
}
