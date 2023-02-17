<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\ForumBundle\Finder;

use Claroline\AppBundle\API\Finder\AbstractFinder;
use Claroline\ForumBundle\Entity\Forum;

class ForumFinder extends AbstractFinder
{
    public static function getClass(): string
    {
        return Forum::class;
    }

    public function getFilters(): array
    {
        return [
            'validationMode' => [
                'type' => 'integer',
                'description' => 'The forum validation mode',
            ],
        ];
    }
}
