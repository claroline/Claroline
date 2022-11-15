<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\AudioPlayerBundle\Controller\API;

use Claroline\AppBundle\Controller\AbstractCrudController;
use Claroline\AudioPlayerBundle\Entity\Resource\Section;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/audioresourcesection")
 */
class SectionController extends AbstractCrudController
{
    public function getName(): string
    {
        return 'audioresourcesection';
    }

    public function getClass(): string
    {
        return Section::class;
    }

    public function getIgnore(): array
    {
        return ['exist', 'copyBulk', 'schema', 'find'];
    }
}
