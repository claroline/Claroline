<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\AudioPlayerBundle\Controller;

use Claroline\AppBundle\Controller\AbstractCrudController;
use Claroline\AudioPlayerBundle\Entity\Resource\Section;
use Symfony\Component\Routing\Annotation\Route;

#[Route(path: '/audio/section', name: 'apiv2_resource_audio_section_')]
class SectionController extends AbstractCrudController
{
    public function getIgnore(): array
    {
        return ['list', 'get'];
    }

    public static function getName(): string
    {
        return 'resource_audio_section_';
    }

    public static function getClass(): string
    {
        return Section::class;
    }
}
