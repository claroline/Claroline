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
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/clacoformentryuser")
 */
class EntryUserController extends AbstractCrudController
{
    public function getClass(): string
    {
        return 'Claroline\ClacoFormBundle\Entity\EntryUser';
    }

    public function getIgnore(): array
    {
        return ['exist', 'copyBulk', 'find', 'list', 'create', 'deleteBulk', 'get', 'csv'];
    }

    public function getName(): string
    {
        return 'clacoformentryuser';
    }
}
