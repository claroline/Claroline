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

#[Route(path: '/clacoformentryuser', name: 'apiv2_clacoformentryuser_')]
class EntryUserController extends AbstractCrudController
{
    public static function getClass(): string
    {
        return 'Claroline\ClacoFormBundle\Entity\EntryUser';
    }

    public function getIgnore(): array
    {
        return ['list', 'create', 'deleteBulk', 'get'];
    }

    public static function getName(): string
    {
        return 'clacoformentryuser';
    }
}
