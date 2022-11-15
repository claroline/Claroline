<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\ThemeBundle\Controller\Icon;

use Claroline\AppBundle\Controller\AbstractCrudController;
use Claroline\ThemeBundle\Entity\Icon\IconSet;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/icon_set")
 */
class IconSetController extends AbstractCrudController
{
    public function getName(): string
    {
        return 'icon_set';
    }

    public function getClass(): string
    {
        return IconSet::class;
    }

    public function getIgnore(): array
    {
        return ['exist', 'copyBulk', 'schema', 'doc', 'find'];
    }
}
