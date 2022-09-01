<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\LinkBundle\Controller;

use Claroline\AppBundle\Controller\AbstractCrudController;
use Claroline\LinkBundle\Entity\Resource\Shortcut;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/shortcut")
 */
class ShortcutController extends AbstractCrudController
{
    public function getClass()
    {
        return Shortcut::class;
    }

    public function getName()
    {
        return 'shortcut';
    }

    public function getIgnore()
    {
        // only keep update action
        return ['list', 'get', 'create', 'deleteBulk', 'copyBulk', 'exist', 'schema', 'find', 'doc', 'export'];
    }
}
